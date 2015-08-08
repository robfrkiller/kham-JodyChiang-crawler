<?php

return [
	//要爬的網址
	'url' => 'http://www.kham.com.tw/buy1.asp?P1=0000058888&P2=%A5%FE%C1p%BA%D6%A7Q%A4%A4%A4%DF-2015%A6%BF%BF%B7%AF%AC%BA%D6%BAt%B0%DB%B7|&P3=&P4=&P5=0&P9=0,0,0,0,0',

	//要哪一縣市的
	'place' => '高雄',

	//可接受最高票價
	'price_max' => 3000,

	//通知資訊
	'notify' => [
		'email' => 'YOUREMAIL',
		'pushbullet' => [
			'device'	=> [
				[
					'name'	=> 'DEVICE_NAME',
					'iden'	=> 'DEVICE_IDEN'
				]
			],
			'authorization' => 'AUTHORIZATION'
		]
	]
];