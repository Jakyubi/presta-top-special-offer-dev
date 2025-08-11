<?php

class BannerManager
{
    public function getBanners($id_lang = null, $onlyEnabled=false)
    {

        $dateNow = date('Y-m-d H:i:s'); //current date
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'specialoffers_banners`';

        if($onlyEnabled){
            $sql .= ' WHERE id_lang='.(int)$id_lang.' AND enabled=1
                    AND (date_start IS NULL OR date_start="0000-00-00 00:00:00" OR date_start <= "'.$dateNow.'")
                    AND (date_end IS NULL OR date_end="0000-00-00 00:00:00" OR date_end >= "'.$dateNow.'")';
        }

        return Db::getInstance()->executeS($sql);

    }

    public function getBannersByGroup($idGroup)
    {
        return Db::getInstance()->executeS('
            SELECT * FROM '._DB_PREFIX_.'specialoffers_banners 
            WHERE id_group = '.(int)$idGroup);
    }

    public function addBanner($bannerData)
    {
        return Db::getInstance()->insert('specialoffers_banners', [
            'id_group' => (int)$bannerData['id_group'],
            'id_lang' => (int)$bannerData['id_lang'],
            'text' => $bannerData['text'],
            'enabled' => (int)$bannerData['enabled'],
            'date_start' => $bannerData['date_start'],
            'date_end' => $bannerData['date_end'],
        ]);
    }

    public function updateBanner($bannerData)
    {
        return Db::getInstance()->update('specialoffers_banners', [
            'text' => $bannerData['text'],
            'enabled' => (int)$bannerData['enabled'],
            'date_start' => $bannerData['date_start'],
            'date_end' => $bannerData['date_end'],
        ], 'id_group = ' . $bannerData['id_group'].' AND id_lang = '.(int)$bannerData['id_lang']);
    }

    public function saveBanner($bannerData)
    {
        $exists = Db::getInstance()->getValue('
            SELECT COUNT(*) FROM '._DB_PREFIX_.'specialoffers_banners
            WHERE id_group = '.(int)$bannerData['id_group'].' AND id_lang = '.(int)$bannerData['id_lang']);

        if($exists){
            return $this->updateBanner($bannerData);
        }else{
            return $this->addBanner($bannerData);
        }
    }


    public function deleteBanner($idGroup)
    {
        return Db::getInstance()->delete('specialoffers_banners', 'id_group = ' . (int)$idGroup);
    }




}