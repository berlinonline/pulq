<?php

class LocalnewsDistrictService extends ProjectBaseService
{
    /**
     * Generates some dummy data as long as there's no working model layer.
     */
    public function getDistricts()
    {
        return $this->districts;
    }

    protected $districts = array(
        "charlottenburg_wilmerdorf" => array(
            "full_name" => "Charlottenburg - Wilmersdorf",
        ),

        "friedrichshain_kreuzberg" => array(
            "full_name" => "Friedrichshain - Kreuzberg",
        ),

        "lichtenberg" => array(
            "full_name" => "Lichtenberg",
        ),

        "marzahn_hellersdorf" => array(
            "full_name" => "Marzahn - Hellersdorf",
        ),

        "mitte" => array(
            "full_name" => "Mitte",
        ),

        "neukoelln" => array(
            "full_name" => "Neukölln",
        ),

        "pankow" => array(
            "full_name" => "Pankow",
        ),

        "reinickendorf" => array(
            "full_name" => "Reinickendorf",
        ),

        "spandau" => array(
            "full_name" => "Spandau",
        ),

        "steglitz_zehlendorf" => array(
            "full_name" => "Steglitz - Zehlendorf",
        ),

        "tempelhof_schoeneberg" => array(
            "full_name" => "Tempelhof - Schöneberg",
        ),

        "treptow_koepenick" => array(
            "full_name" => "Treptow - Köpenick",
        ),
    );
    
    public function getDistrictByName($name)
    {
        if (isset($this->districts[$name]))
        {
            return $this->districts[$name];
        }
        else
        {
            throw new DistrictNotFoundException($name);
        }
    }

}
