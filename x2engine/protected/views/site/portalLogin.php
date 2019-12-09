<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




$this->pageTitle = Yii::app()->name . ' - Login';
LoginThemeHelper::init();

$loginBoxHeight = 210;

if (X2_PARTNER_DISPLAY_BRANDING) {
    $loginBoxHeight -= 23;
}

if ($model->useCaptcha) {
    $loginBoxHeight -= 56;
}

Yii::app()->clientScript->registerCss('loginExtraCss', "

#login-box-outer {
    margin-top: " . $loginBoxHeight . "px;
    width: 331px !important;
}
#LoginForm_twoFactorCode {
    font-size: larger;
    text-align: center;
    width: 40%;
}
#login-form-logo {
    font-size: 64px;
}
.errorMessage {
    color: #dc3545 !important;
}
#signin-button-container>button {
    background: #AEAEAE;
}
");

Yii::app()->clientScript->registerScript('loginPageJS', "
;(function () {

document.getElementById('LoginForm_username').focus (); // for when autofocus isn't supported

var mobileLoginUrl = '" . Yii::app()->getBaseUrl() . '/index.php/mobile/login' . "';
    
$('#mobile-signin-button').click (function () {
    $('#login-form-outer').attr ('action', mobileLoginUrl);
});

$('#LoginForm_rememberMe').click (function () {
    if ($('input[type=\'checkbox\']').is(':checked')) {
        document.cookie = 'rememberMe=on;max-age=2592000;path=/;';
    } else {
        document.cookie = 'rememberMe=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;';
    }
});

$('.change-user').click (function () {
    document.cookie = 'rememberMe=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;';
});

$('#login-form-outer').submit(function(evt) {
    evt.preventDefault();
    var that = this,
        username = $('#LoginForm_username').val(),
        csrfTokenRegex = /(?:^|.*;)\s*YII_CSRF_TOKEN\s*=\s*([^;]*)(?:.*$|$)/;
    var csrfToken = document.cookie.replace (csrfTokenRegex, '$1');

    $.ajax({
        url: '" . Yii::app()->createUrl('/site/site/needsTwoFactor') . "',
        type: 'POST',
        data: {
            username: username,
            YII_CSRF_TOKEN: csrfToken
        },
        success: function(data) {
            $(that).unbind('submit');
            if (data != 'yes')
                $(that).submit();
            else {
                $('.twoFactorCodeControls').slideDown();
                $('#LoginForm_twoFactorCode').focus();
            }
        },
        error: function(data) {
            console.log('error checking 2FA requirement');
            $(that).unbind('submit');
        }
    });
});
}) ();
", CClientScript::POS_READY);

Yii::app()->clientScript->registerGeolocationScript();
?>

<div id="login-box-outer" class="container">
    <div class="d-flex justify-content-center align-items-center<?php echo ($profile ? ' welcome-back-page' : ''); ?>" id="login-page">
        <div id="login-box" class="bg-white rounded shadow pt-2 px-5" >
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'login-form-outer',
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
                'clientOptions' => array(
                    'validateOnSubmit' => false,
                ),
            ));
            ?>
            <div class="" id="login-form">
                <?php
                if (isset($_POST['themeName'])) {
                    echo CHtml::hiddenField('themeName', $_POST['themeName']);
                }
                ?>

                <div class="text-center">
                    <div class="cell form-cell" id="login-form-inputs-container"></div>

                    <?php
                    echo '<h2 id="portal-label" class="text-black-0">'. Yii::t('application','Portal Login') .'</h2>';
                    $loginLogo = Media::getLoginLogo();
                    if ($loginLogo) {
                        echo CHtml::link(CHtml::image(
                                        $loginLogo->getPublicUrl(), Yii::app()->settings->appName, array(
                                    'id' => 'custom-login-form-logo',
                                )), 'https://www.x2crm.com/', array('class' => 'login-logo-link'));
                    } else {
                        echo CHtml::link(X2Html::logo('login_' .
                                        (LoginThemeHelper::singleton()->usingDarkTheme ? 'white' : 'black'), array(
                                    'id' => 'login-form-logo',
                                )), 'https://www.x2crm.com/', array('class' => 'login-logo-link'));
                    }
                    if ($profile) {
                        echo '<h3 id="full-name" class="text-black-50">' . $profile->fullName . '</h3>';
                    }
                    ?>

                    <div class=''>

                        <?php
                        /*
                        if (!$profile) {
                            echo $form->checkBox(
                                $model, 'rememberMe', 
                                array('value' => '1', 'uncheckedValue' => '0'));
                            echo $form->label(
                                    $model, 'username', array(
                                'style' => ($profile ? 'display: none;' : '')));
                        }
                         * 
                         */

                        if ($profile) {
                            echo $form->hiddenField($model, 'username');
                        } else {
                            echo $form->textField($model, 'username', array(
                                'class' => 'form-control my-3',
                                'placeholder' => Yii::t('app', 'Username')
                            ));
                        }

                        setcookie('isMobileApp', 'false'); // save cookie
                        ?>

                    </div>

                    <div class=''>

                        <?php
                        if (AuxLib::getIEVer() < 10) {
                            echo $form->label($model, 'password', array('style' => 'margin-top:5px;'));
                        }
                        echo $form->passwordField(
                                $model, 'password', array(
                            'class' => 'form-control mb-3',
                            'placeholder' => Yii::t('app', 'Password')
                        ));
                        echo $form->error($model, 'password');
                        ?>

                    </div>

                    <?php
                    if ($model->useCaptcha && CCaptcha::checkRequirements()) {
                        ?>
                        <div class="captcha-row">
                            <div id="captcha-container">
                                <?php
                                $this->widget('CCaptcha', array(
                                    'clickableImage' => true,
                                    'showRefreshButton' => false,
                                    'imageOptions' => array(
                                        'id' => 'captcha-image',
                                        'style' => 'display:block;cursor:pointer;',
                                        'title' => Yii::t('app', 'Click to get a new image')
                                    )
                                ));
                                ?>
                            </div>
                            <p class="hint"><?php echo Yii::t('app', 'Please enter the letters in the image above.'); ?></p>
                            <?php
                            echo $form->textField($model, 'verifyCode');
                            ?>
                        </div>
                    <?php } ?>
                    <div class="twoFactorCodeControls" style="display:none;">
                        <p class="hint"> <?php Yii::t('app', 'Please enter your two factor authentication verification code.'); ?></p>
                        <?php echo $form->textField($model, 'twoFactorCode', array('autocomplete' => 'off')); ?>
                    </div>
                    <div class="" id='signin-button-container'>
                        <button class='btn text-white form-control' id='signin-button'>
                            <?php echo Yii::t('app', 'Sign in'); ?>
                        </button>
                        <div class='clearfix'></div>
                    </div>
                    <div class='remember-me-row d-flex pt-3'>
                        <div class="cell remember-me-cell mr-auto">
                            <?php
                            if ($model->rememberMe) {
                                echo $form->hiddenField($model, 'rememberMe', array('value' => 1));
                                ?>
                                <a href="<?php echo Yii::app()->createUrl('/site/site/forgetMe'); ?>"
                                   class="x2-link x2-minimal-link text-link change-user">
                                       <?php echo Yii::t('app', 'Change User'); ?>
                                </a>
                                <?php
                            } else {
                                $model->rememberMe = true;
                                echo $form->checkBox(
                                        $model, 'rememberMe', array('value' => '1', 'uncheckedValue' => '0'));
                                echo $form->label(
                                        $model, 'rememberMe', array());
                                echo $form->error($model, 'rememberMe');
                            }
                            ?>
                        </div>
                        <div class="cell need-help-cell">
                            <?php
                            echo CHtml::link(Yii::t('app', 'Need help?'), array('/site/resetPassword'), array('class' => 'x2-minimal-link help-link text-link'));
                            ?>
                        </div>
                        <?php
                        if (Yii::app()->settings->googleIntegration) {
                            ?>
                            <div class="cell google-login-cell">
                                <?php
                                echo CHtml::link('<img src="' . Yii::app()->theme->baseUrl . '/images/google_icon.png" id="google-icon" /><span>' . Yii::t('app', 'Sign in with Google') . '</span>', (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .
                                        ((substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST']) .
                                        $this->createUrl('/site/googleLogin'), array('class' => 'alt-sign-in-link google-sign-in-link text-link'));
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <input type="hidden" name="geoCoords" id="geoCoords">
                </div><!-- #login-form-inputs-container -->
            </div><!-- .row -->
        </div><!-- # login-form -->
        <?php $this->endWidget(); ?>
    </div><!-- #login-box -->
</div><!-- #login-page -->


</div>

