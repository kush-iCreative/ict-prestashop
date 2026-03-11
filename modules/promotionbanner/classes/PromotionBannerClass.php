<?php

class PromotionBannerClass extends ObjectModel
{
    public $id_banner;
    public $title;
    public $description;
    public $cta_link;
    public $category_id;
    public $image;
    public $start_date;
    public $end_date;
    public $status;

    public static $definition = [
        'table' => 'promotion_banner',
        'primary' => 'id_banner',
        'fields' => [
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'cta_link' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'size' => 255],
            'category_id' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
           'image' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'start_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'end_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];
}
