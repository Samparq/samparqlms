<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 23/5/18
 * Time: 12:34 PM
 */

?>


<div class="row">
<div class="chatback">
<?php foreach ($chats as $chat): ?>
Sender Name: <?= Yii::$app->samparq->getUsernameById($chat->sender_id) ?>:<br/>
    Message: <?= $chat->message; ?><br/>
<?php endforeach; ?>

<div class="contentbox">

    <div class="chatdiv">
<div class="left_bubble">
 
  <?= $chat->message; ?> 
    Hello How are you??<br>
    Hi manish
 
</div>
</div>
<div class="chatdiv">
<div class="right_bubble">
    Hello How are you??<br>
    Hi manish    
</div>
</div>

<div class="chatdiv">
<div class="left_bubble"> 
 <?= $chat->message; ?> 
   Hello How are you??<br>
   Hi manish
</div>
</div>
<div class="chatdiv">
<div class="right_bubble">
   Hello How are you??<br>
   Hi manish   
</div>
</div>

<div class="chatdiv">
<div class="left_bubble"> 
 <?= $chat->message; ?> 
   Hello How are you??<br>
   Hi manish
</div>
</div>

<div class="chatdiv">
<div class="right_bubble">
   Hello How are you??<br>
   Hi manish   
</div>
</div>

    <div class="chatdiv">
<div class="left_bubble">
 
  <?= $chat->message; ?> 
    Hello How are you??<br>
    Hi manish
 
</div>
</div>
<div class="chatdiv">
<div class="right_bubble">
    Hello How are you??<br>
    Hi manish    
</div>
</div>

<div class="chatdiv">
<div class="left_bubble"> 
 <?= $chat->message; ?> 
   Hello How are you??<br>
   Hi manish
</div>
</div>
<div class="chatdiv">
<div class="right_bubble">
   Hello How are you??<br>
   Hi manish   
</div>
</div>

<div class="chatdiv">
<div class="left_bubble"> 
 <?= $chat->message; ?> 
   Hello How are you??<br>
   Hi manish
</div>
</div>

<div class="chatdiv">
<div class="right_bubble">
   Hello How are you??<br>
   Hi manish   
</div>
</div>

</div>


</div>
 
<footer class="chatfooter">
    <textarea name="" id="" cols="30" rows="1" placeholder="Type here..." name="comments" maxlength="1000"></textarea>
    <a href="javascript:void(0)"> <img src="../images/send.png" alt=""></a>
</footer>
 
</div>