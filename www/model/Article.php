<?php
namespace plushka\model;
use plushka\core\plushka;

/**
 * Помощник для выборки статей и категорий статей
 */
class Article {

    public const DEFAULT_CATEGORY_FIELDS='id,title,onPage,text1,metaTitle,metaKeyword,metaDescription';
    public const ARTICLE_ONE_FIELDS='id,title,metaTitle,metaKeyword,metaDescription,date,text2';
    public const ARTICLE_LIST_FIELDS='id,alias,title,text1,date';

    /**
     * Находит категорию по псевдониму
     * @param string $alias Псевдоним категории
     * @param string $fieldList Список полей
     * @return array|null Данные категории
     */
    public static function categoryByAlias(string $alias,string $fieldList=self::DEFAULT_CATEGORY_FIELDS): ?array {
        $db=plushka::db();
        $data=$db->fetchArrayOnceAssoc('SELECT '.$fieldList.' FROM article_category_'._LANG.' WHERE alias='.$db->escape($alias));
        if($data!==null) $data['alias']=$alias;
        return $data;
    }

    /**
     * Находит статью по псевдониму
     * @param string $alias Псевдоним статьи
     * @param string $fieldList Список полей
     * @return array|null Данные статьи
     */
    public static function articleByAlias(string $alias,string $fieldList=self::ARTICLE_ONE_FIELDS): ?array {
        $db=plushka::db();
        $data=$db->fetchArrayOnceAssoc('SELECT '.$fieldList.' FROM article_'._LANG.' WHERE alias='.$db->escape($alias).' AND (date=0 OR date<'.time().')');
        if($data===null) return null;
        $data['alias']=$alias;
        $data['date']=(int)$data['date'];
        return $data;
    }

    public static function articleList(int $categoryId,int $limit,string $fieldList=self::ARTICLE_LIST_FIELDS): array {
        $db=plushka::db();
        $data=$db->fetchArrayAssoc('SELECT '.$fieldList.' FROM article_'._LANG.' WHERE categoryId='.$categoryId.' AND (date=0 OR date<'.time().') ORDER BY sort,date DESC,id DESC',$limit);
        for($i=0,$cnt=count($data);$i<$cnt;$i++) $data[$i]['date']=(int)$data[$i]['date'];
        return $data;
    }

    public static function foundRows(): int {
        return plushka::db()->foundRows();
    }

}
