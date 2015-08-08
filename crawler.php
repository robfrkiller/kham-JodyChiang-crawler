<?php
$pg_stime = microtime(true);
include 'vendor/autoload.php';
$config = require 'config.php';
use Sunra\PhpSimple\HtmlDomParser;

$html = HtmlDomParser::file_get_html($config['url']);
$find = $html->find('.border-g2', 1)->find('select');
$buy = $nobuy = [];
foreach ($find as $e) {
	$select_td = $e->parent();
	$place_td = $select_td->prev_sibling();
	$place = iconv('big5', 'utf-8', $place_td->innertext);
	if (!strstr($place, $config['place'])) {
		continue;
	}
	$date_td = $place_td->prev_sibling();
	$link_td = $select_td->next_sibling()->next_sibling();
	preg_match_all('/href=(.*)><img/', $select_td->next_sibling()->next_sibling()->innertext, $match);

	if (isset($match[1][0])) {
		$link = $match[1][0];
	} else {
		$link = '';
	}
	$date = $date_td->innertext;
	$options = $e->find('option');
	foreach ($options as $op) {
		$pay = iconv('big5', 'utf8', $op->innertext);
		preg_match_all('/[1-6]?800/', $pay, $out);
		if (isset($out[0][0])) {
			$price = $out[0][0] + 0;
		} else {
			$price = 9999;
		}
		if (strstr($pay, '剩位')) {
			if ($price < $config['price_max']) {
				$buy[] = [$date, $pay, $link];
			} else {
				$nobuy[] = [$date, $pay, $link];
			}
		}
	}
}

if (isset($buy[0])) {
	$table = '<table border="1"><tr><td>日期</td><td>位置價格</td><td>link</td></tr>';
	$bullet = '';
	foreach ($buy as $b) {
		$table .= '<tr><td><font color="red">' . $b[0] . '</font></td><td>' . $b[1] . '</td><td><a href="http://www.kham.com.tw/' . $b[2] . '" target="_blank">click</a></td></tr>';
		$bullet .= $b[0] . '_'.$b[1] . 'http://www.kham.com.tw/' . $b[2] . '\r\n';
	}
	$table .= '</table>';
	if (isset($nobuy[0])) {
		$table .= '<br><br>不買的<br>';
		$table .= '<table border="1"><tr><td>日期</td><td>位置價格</td><td>link</td></tr>';
		$bullet .= '\r\n\r\n\r\n\r\n不買的\r\n';
		foreach ($nobuy as $b) {
			$table .= '<tr><td>' . $b[0] . '</td><td>' . $b[1] . '</td><td><a href="http://www.kham.com.tw/' . $b[2] . '" target="_blank">click</a></td></tr>';
			$bullet .= $b[0] . '_' . $b[1] . 'http://www.kham.com.tw/' . $b[2] . '\r\n';
		}
		$table .= '</table>';
	}
	$mail = new PHPMailer;

	$mail->SMTPDebug = 2;

	$mail->From = 'bot@skynet.com';
	$mail->FromName = '全自動機器人';
	$mail->CharSet = 'UTF-8';
	$mail->addAddress($config['notify']['email']);
	$mail->setLanguage('zh');

	$mail->isHTML(true);

	$mail->Subject = '寬宏-江蕙清票通知';
	$mail->Body    = $table;

	if($mail->send()) {
		echo '寄送成功';
	} else {
		echo '寄送失敗，原因：' . $mail->ErrorInfo;
	}
	echo PHP_EOL;

	//pushbullet api
	$client = new GuzzleHttp\Client();
	foreach ($config['notify']['pushbullet']['device'] as $push) {
		$req = $client->post('https://api.pushbullet.com/v2/pushes', [
			'headers'	=> [
				'Authorization'	=> $config['notify']['pushbullet']['authorization'],
				'Content-type'	=> 'application/json'
			],
			'body'		=> '
				{
					"device_iden": "' . $push['iden'] . '",
					"type": "note",
					"title": "寬宏-江蕙清票通知",
					"body": "' . $bullet . '"
				}'
		]);
		if ($req->getStatusCode() === 200) {
			$body = $req->getBody();
			$res = json_decode($body->read(9999), 1);
			if ($res['active'] === true) {
				echo $push['name'] . ' push done.' . PHP_EOL;
			}
		}
	}
}
$pg_etime = microtime(true);
echo date('Y-m-d H:i:s') . ' - 寄出 ' . count($buy) . ' 個' . PHP_EOL;
echo '執行時間(s): ' . ($pg_etime - $pg_stime) . PHP_EOL;

$html->clear();
