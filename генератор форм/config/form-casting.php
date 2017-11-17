<?php return array(
	'field'=>array(
		'name'=>array('text','Name','validate'=>array('string','name',true)),
		'age'=>array('text','Age','validate'=>array('integer','age',true,'min'=>21,'max'=>60)),
		'height'=>array('text','Height','validate'=>array('integer','height',true,'min'=>140,'max'=>250)),
		'measurements'=>array('text','Measurement','validate'=>array('string','measurements',true)),
		'hair'=>array('text','Hair color','validate'=>array('string','hair color',true)),
		'eye'=>array('text','Eyes color','validate'=>array('string','eyes color',true)),
		'nationality'=>array('text','Nationality','validate'=>array('string','nationality',true)),
		'city'=>array('text','Home city','validate'=>array('string','home city',true)),
		'email'=>array('text','E-mail','validate'=>array('email','e-mail',true)),
		'phone'=>array('text','Phone','validate'=>array('string','phone',true)),
		'description'=>array('textarea','Personal description','validate'=>array('string','personal description',true)),
		'experience'=>array('textarea','Your escort experience or expectations','validate'=>array('string','your escort experience or expectations')),
		'photo'=>array('file','Photo upload',true),
		'captcha'=>array('captcha','Verification code','validate'=>array('captcha','verification code'))
	)
);