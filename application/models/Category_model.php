<?php 

class Category_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if($result->image && $result->image != "") {
                $result->image = base_url() . UPLOAD_CATEGORY_IMAGE . $result->image;
            }
        }

        return $result;
    }

}