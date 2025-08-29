<?php



class BannerManager
{
   public function getBanners($id_lang = null, $onlyEnabled=false, $sort = 'id_group', $order='ASC', $filters = [])
    {
        $dateNow = date('Y-m-d H:i:s');
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('specialoffers_banners');


        if($onlyEnabled){
            $sql->where('id_lang = ' . (int)$id_lang);
            $sql->where('enabled = 1');
            $sql->where('(date_start IS NULL OR date_start="0000-00-00 00:00:00" OR date_start <= "'.pSQL($dateNow).'")');
            $sql->where('(date_end IS NULL OR date_end="0000-00-00 00:00:00" OR date_end >= "'.pSQL($dateNow).'")');
        }


        foreach($filters as $field => $value){
            if (empty($value) && !is_numeric($value)) {
                continue;
            }

            if (in_array($field, ['id_banner', 'id_group', 'id_lang', 'enabled'])) {
                $sql->where("$field = " . (int)$value);
            } elseif (in_array($field, ['text'])) {
                $sql->where("$field LIKE '%" . pSQL($value) . "%'");
            } elseif (in_array($field, ['date_start', 'date_end'])) {
                if (is_array($value)) {
                    if (!empty($value[0])) {
                        $sql->where("$field >= '" . pSQL($value[0]) . "'");
                    }
                    if (!empty($value[1])) {
                        $sql->where("$field <= '" . pSQL($value[1]) . "'");
                    }
                } else {
                    $sql->where("$field = '" . pSQL($value) . "'");
                }
            }
        }



        $allowedSortFields = ['id_banner', 'id_group', 'id_lang', 'date_start', 'date_end', 'enabled'];
        if(!in_array($sort, $allowedSortFields)){
            $sort = 'id_group';
        }

        $order = strtoupper($order);
        if(!in_array($order, ['ASC', 'DESC'])){
            $order = 'ASC';
        }

        $sql->orderBy($sort . ' ' . $order);
        return Db::getInstance()->executeS($sql);
    }

    public function countBanners($id_lang = null, $onlyEnabled = false)
    {
        $dateNow = date('Y-m-d H:i:s');
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('specialoffers_banners');

        if($onlyEnabled){
            $sql->where('id_lang = ' . (int)$id_lang);
            $sql->where('enabled = 1');
            $sql->where('(date_start IS NULL OR date_start="0000-00-00 00:00:00" OR date_start <= "'.pSQL($dateNow).'")');
            $sql->where('(date_end IS NULL OR date_end="0000-00-00 00:00:00" OR date_end >= "'.pSQL($dateNow).'")');
        }

        return (int)Db::getInstance()->getValue($sql);

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
            'link' => $bannerData['link'],
            'enabled' => (int)$bannerData['enabled'],
            'date_start' => $bannerData['date_start'],
            'date_end' => $bannerData['date_end'],
        ]);
    }

    private function updateBanner($bannerData)
    {
        return Db::getInstance()->update('specialoffers_banners', [
            'text' => $bannerData['text'],
            'link' => $bannerData['link'],
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