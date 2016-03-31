<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that front page works');
$I->amOnPage('/');
$I->see('Home');
$I->click('To be happy');
$I->see('to be happy');
