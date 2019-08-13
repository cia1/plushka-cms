<div class="article">
    <?php if($this->article['date']!==0) { ?>
        <span class="date"><?=date('d.m.Y',$this->article['date'])?></span>
    <?php } ?>
    <?=$this->article['text2']?>
</div>
