<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/6/18
 * Time: 12:15 PM
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <title>Account activation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body bgcolor="#f2f2f2" style="margin:0; padding:0;">
<table bgcolor="#f2f2f2" width="100%" style="font:14px/20px Arial, Helvetica, sans-serif; color:#000; min-width:320px;" cellspacing="0"
       cellpadding="0">
    <tr>
        <td>
            <table bgcolor="#ffffff" width="600" align="center" style="background: #ffffff; margin:0 auto;" cellpadding="0" cellspacing="0">
                <!-- header -->
                <tr>
                    <td align="center">
                        <a style="text-decoration:none;">
                            <img editable="true" src="<?= Yii::$app->params['images'] ?>/header.png" width="600" alt="APPRAISAL PROCESS!!!" />
                        </a>
                    </td>
                </tr>
                <!-- content -->
                <tr>
                    <td>
                        <!-- section -->
                        <table style="background: #ffffff" bgcolor="#ffffff" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:10px 0px 40px; ">
                                    <table width="96%" align="center" cellpadding="0" cellspacing="0">


                                        <tr>
                                            <td style="font:14px/20px Arial, Helvetica, sans-serif; color:#000;">


                                                <br>Hi <?= $data['client_name'] ?>
                                                <br>
                                                <br>Please use below credentials to login into your account.The below password is valid for one time login,you have to change your password once you logged in.Please do not share this email to anyone.
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <!-- section -->
                        <table bgcolor="#ffffff" width="96%" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <br>
                                <td align="center" style="font:16px/20px Arial,   Helvetica, sans-serif; font-weight: bold; color:#3b3b3b;">

                                    Your credentials are mentioned below.
                                    <br> <br>

                                </td>
                            </tr>

                            <tr>
                                <td style="padding:8px 0px; background: #eaeaea; margin:  0 15px" align="">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">


                                                <table>
                                                    <tr>
                                                        <td align="center" valign="middle" style="padding-left: 10px">
                                                            <img src="<?= Yii::$app->params['images'] ?>/user.png" alt="">
                                                        </td>
                                                        <td  valign="middle" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">
                                                            Code: <?= $data['client_code'] ?>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                            <td align="" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">


                                                <table>
                                                    <tr>
                                                        <td align="center" valign="middle" style="padding-left: 10px">
                                                            <img src="<?= Yii::$app->params['images'] ?>/user.png" alt="">
                                                        </td>
                                                        <td  valign="middle" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">
                                                            Email: <?= $data['client_email'] ?>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                            <td align="" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">


                                                <table>
                                                    <tr>
                                                        <td align="center" valign="middle">
                                                            <img src="<?= Yii::$app->params['images'] ?>/lock.png" alt="">
                                                        </td>
                                                        <td valign="middle" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">
                                                            Password: <?= $data['client_pwd'] ?>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td align="center">
                                    <br><br><br>
                                    <a href="" style="    background: url(button.jpg);
                                        width: 367px;
                                        height: 52px;
                                        display: block;
                                        text-align: center;
                                        background-repeat: no-repeat;
                                        line-height: 54px;
                                        color: #ffffff;
                                        text-decoration: none;
                                        font-size: 18px;">
                                        Go to Admin Panel
                                    </a>

                                </td>
                            </tr>

                        </table>
                        <!-- section -->
                        <table bgcolor="#ffffff" width="96%" cellpadding="0" cellspacing="0" align="center">
                            <tr>
                                <td style="padding:35px 0;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="font:14px/20px Arial, Helvetica, sans-serif; color:#242424;">
                                                If you had not requested this,please email us on
                                                <span style="color:#0547c8">support@samparq.com</span>.
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <!-- footer -->
                <tr>
                    <td style="padding:40px 30px 30px; background: #eeeeee;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="font:12px/22px Arial, Helvetica, sans-serif; color:#7c7c7c;">
                                    Email sent by Team Samparq
                                    <br/> Copyright © <?= date('Y')?>, All rights reserved.
                                    <br/> Powered by Qdegrees
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>
