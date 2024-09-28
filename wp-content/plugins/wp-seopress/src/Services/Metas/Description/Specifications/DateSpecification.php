<?php

namespace SEOPress\Services\Metas\Description\Specifications;


class DateSpecification
{

    const NAME_SERVICE = 'DateDescriptionSpecification';

    /**
     * @param array $params [
     *     'context' => array
     *
     * ]
     * @return string
     */
    public function getValue($params) {
        $value = seopress_get_service('TitleOption')->getArchivesDateDesc();

        if(empty($value) || !$value){
            return "";
        }

        $context = $params['context'];

        return seopress_get_service('TagsToString')->replace($value, $context);
    }



    /**
     *
     * @param array $params [
     *     'post' => \WP_Post
     *     'description' => string
     *     'context' => array
     *
     * ]
     * @return boolean
     */
    public function isSatisfyBy($params)
    {
        $context = $params['context'];

        if(!$context['is_date']){
            return false;
        }

        $value = seopress_get_service('TitleOption')->getArchivesDateDesc();

        if(empty($value)){
            return false;
        }

        return true;

    }
}


