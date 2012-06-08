<?php

class LocalnewsService extends ProjectBaseService
{
    protected $dummyTexts = array();

    public function getDummyTexts()
    {
        if (empty($this->dummyTexts))
        {
            $lorem = file_get_contents('http://lorem-ipsum.me/api/text');

            $this->dummyTexts = preg_split('/[\n\r]+/', $lorem);
        }

        return $this->dummyTexts;
    }

    public function getNewsByDistrict($district, $limit = 5)
    {
        $items = array();

        for ($i = 0; $i < $limit; $i++)
        {
            $items[] = $this->generateDummyNewsItem();
        }

        return $items;
    }

    protected function generateDummyNewsItem()
    {

        $dummyTexts = $this->getDummyTexts();
        $item = $dummyTexts[array_rand($dummyTexts)];

        $headline = strtok($item, ' ') . ' ' . strtok(' ');

        return array(
            'headline' => $headline,
            'body' => $item,
        );
    }

}
