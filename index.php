
<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if (!isset($_GET['username'])) {
  echo json_encode(['error' => 'Username not provided']);
  exit;
}

$username = preg_replace('/[^a-zA-Z0-9_.]/', '', $_GET['username']);
$target = "https://www.instagram.com/{$username}/";

$api_key = "QV6GOW1EB33TCXMMSF82V7HTNX159348QXW1USRU4HA1EYEDARRRJPNSAOMNAIKEKS05OPXC59XPGHX9";
$url = "https://app.scrapingbee.com/api/v1/?api_key={$api_key}&url=" . urlencode($target) . "&render_js=true";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200 || !$response) {
  echo json_encode(['error' => 'Failed to fetch profile']);
  exit;
}

if (!preg_match('/window._sharedData = (.*?);<\/script>/s', $response, $m)) {
  echo json_encode(['error' => 'Profile structure changed.']);
  exit;
}

$data = json_decode($m[1], true);
$user = $data['entry_data']['ProfilePage'][0]['graphql']['user'] ?? null;

if (!$user) {
  echo json_encode(['error' => 'Could not locate user data.']);
  exit;
}

echo json_encode([
  'username' => $user['username'],
  'profile_pic_url' => $user['profile_pic_url_hd'] ?? $user['profile_pic_url'],
  'biography' => $user['biography'],
  'followers' => $user['edge_followed_by']['count'],
  'following' => $user['edge_follow']['count'],
  'posts' => $user['edge_owner_to_timeline_media']['count']
]);
?>
