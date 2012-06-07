<?php

class LocalnewsDistrictValidator extends AgaviStringValidator
{
    protected function validate()
    {
        if (!parent::validate())
        {
            return false;
        }

        $districtName = $this->getData($this->getArgument());

        $districtService = new LocalnewsDistrictService();

        try
        {
            $district = $districtService->getDistrictByName($districtName);
            $this->export($district, $this->getArgument());

            return true;
        }
        catch (DistrictNotFoundException $e)
        {
            return false;
        }
    }
}
