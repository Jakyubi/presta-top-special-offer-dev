<?php



class BannerManager
{
    public function getBanners($id_lang = null, $onlyEnabled=false)
    {

        $dateNow = date('Y-m-d H:i:s');
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('specialoffers_banners');

        if($onlyEnabled){
            $sql->where(
            'id_lang = ' . (int)$id_lang .
            ' AND enabled = 1 ' .
            ' AND (date_start IS NULL OR date_start="0000-00-00 00:00:00" OR date_start <= "'.pSQL($dateNow).'")' .
            ' AND (date_end IS NULL OR date_end="0000-00-00 00:00:00" OR date_end >= "'.pSQL($dateNow).'")');
        }

        return Db::getInstance()->executeS($sql);

    }

    public function getBannersByGroup($idGroup)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('specialoffers_banners');
        $sql->where('id_group = '.(int)$idGroup);

        return Db::getInstance()->executeS($sql);
    }

    private function addBanner($bannerData)
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

    private function updateBanner($bannerData)
    {
        return Db::getInstance()->update('specialoffers_banners', [
            'text' => $bannerData['text'],
            'enabled' => (int)$bannerData['enabled'],
            'date_start' => $bannerData['date_start'],
            'date_end' => $bannerData['date_end'],
        ], 'id_group = ' . (int)$bannerData['id_group'].' AND id_lang = '.(int)$bannerData['id_lang']);
    }

    public function saveBanner($bannerData)
    {
        $bannerData = $this->validateBannerData($bannerData);

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('specialoffers_banners');
        $sql->where('id_group = '.(int)$bannerData['id_group']. ' AND id_lang = '.(int)$bannerData['id_lang']);

        $exists = Db::getInstance()->getValue($sql);

        if($exists){
            return $this->updateBanner($bannerData);
        }else{
            return $this->addBanner($bannerData);
        }
    }


    public function deleteBanner($idGroup)
    {
        if (!is_numeric($idGroup)) {
            throw new InvalidArgumentException('Invalid id_group for delete.');
        }

        return Db::getInstance()->delete('specialoffers_banners', 'id_group = ' . (int)$idGroup);
    }

    private function validateBannerData($bannerData)
    {
        if(!isset($bannerData['id_group']) || !is_numeric($bannerData['id_group'])){
            throw new InvalidArgumentException('Invalid or missing id_group');
        }

        if(!isset($bannerData['id_lang']) || !is_numeric($bannerData['id_lang'])){
            throw new InvalidArgumentException('Invalid id_lang');
        }

        if(isset($bannerData['text']) && Tools::strlen($bannerData['text']) > 2048){
            throw new InvalidArgumentException('Text is too long');
        }

        $bannerData['text'] = Tools::purifyHTML(
            isset($bannerData['text']) ? $bannerData['text'] : ''
        );

        $bannerData['enabled'] = isset($bannerData['enabled']) && $bannerData['enabled'] ? 1 : 0;

        $bannerData['date_start'] = isset($bannerData['date_start']) && $this->validateDate($bannerData['date_start']) ? $bannerData['date_start'] : null;
        $bannerData['date_end'] = isset($bannerData['date_end']) && $this->validateDate($bannerData['date_end']) ? $bannerData['date_end'] : null;

        return $bannerData;
    }

    private function validateDate($date)
    {
        if(empty($date)){
            return true;
        }

        $d = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }




}