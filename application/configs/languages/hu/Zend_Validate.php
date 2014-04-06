<?php
return array(
	Zend_Validate_NotEmpty::IS_EMPTY=>'A mező kitöltése kötelező',
	Zend_Validate_Alnum::NOT_ALNUM=>'Csak betűket és számokat tartalmazhat',
	Zend_Validate_StringLength::TOO_LONG=>'Maximum %max% karakterből kell állnia',
	Zend_Validate_StringLength::TOO_SHORT=>'Minimum %min% karakterből kell állnia',
	Zend_Validate_EmailAddress::INVALID_FORMAT=>'Valós e-mail címet adj meg',
	Zend_Captcha_Image::BAD_CAPTCHA=>'Rossz ellenörzőkód',
	Zend_Validate_Digits::NOT_DIGITS=>'Csak számokat tartalmazhat',
	Zend_Validate_Between::NOT_BETWEEN=>'%min% és %max% között kell lennie',
	Zend_Validate_Regex::NOT_MATCH=>'Nem megfelelő a formátum',
	Zend_Validate_File_Count::TOO_MANY=>'Csak %max% fájlt tölthető fel',
	Zend_Validate_File_Size::TOO_BIG=>'Maximális feltöltési méret: %max%',
	Zend_Validate_File_Extension::FALSE_EXTENSION=>'Csak a következő típusokat lehet feltölteni: %extension%',
	Zend_Validate_File_ImageSize::HEIGHT_TOO_SMALL=>'Minimális méret: %minwidth%x%minheight px',
	Zend_Validate_File_ImageSize::WIDTH_TOO_SMALL=>'Minimális méret: %minwidth%x%minheight px'
);
