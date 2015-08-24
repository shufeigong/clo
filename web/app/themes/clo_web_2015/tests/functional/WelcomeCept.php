<?php
$I = new FunctionalTester($scenario);
$I->wantTo('perform actions and see result');
$I->amOnPage('/');
$I->see('Home');
$I->click('To be happy');
$I->see('to be happy');
