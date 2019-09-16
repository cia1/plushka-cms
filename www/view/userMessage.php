<?php
use plushka\core\plushka;
use plushka\model\User;
?>
<?php if(empty($this->messageList)===true) { ?>
    <p class="newMessageCount empty"><?=LNGYouHaveNotMessages?></p>
<?php } else { ?>
    <p class="newMessageCount">
        <?=$this->newMessageCount<1 ? LNGThereAreNoNewMessages : sprintf(LNGYouHaveNewMessages,$this->newMessageCount)?>
    </p>
    <div class="messageList">
    <?php foreach($this->messageList as $index=>$item) { ?>
        <div class="row<?=$item['direction']?><?=($item['isNew'] ? ' newMessage' : '')?>">
            <p class="title"><?=$item['subjectDirection']?> <?=$item['login']?><span><?=date('d.m.Y H:i',$item['date'])?></span></p>
            <div class="content"><?=$item['message']?></div>
            <?php if($item['direction']===User::MESSAGE_DIRECTION_TO) { ?>
                <p class="control"><a href="#" onclick="return showAnswerForm(<?=$index?>);" class="button"><?=LNGAnswer?></a></p>
            <?php } ?>
        </div>
        <?php if($item['direction']===User::MESSAGE_DIRECTION_TO) {
            echo '<div class="answer" id="answer'.$index.'" style="display:none;">';
            $f=plushka::form();
            $f->hidden('replyTo',$item['id']);
            $f->textarea('message','');
            $f->submit(LNGSend);
            $f->render();
            echo '</div>';
        }
    } ?>
    </div>
    <script>
    function showAnswerForm(index) {
        let o=document.getElementById('answer'+index);
        if(!o.style.display) o.style.display='none'; else {
            o.style.display='';
            o.getElementsByTagName('textarea')[0].focus();
        }
        return false;
    }
    </script>
<?php } ?>
