<div class="list">
    <?=$this->category['text1']?>
    <?php foreach($this->articles as $item) { ?>
        <p><a href="<?=plushka::link('article/list/'.$this->category['alias'].'/'.$item['alias'])?>"><?=$item['title']?></a></p>
    <?php } ?>
</div>
