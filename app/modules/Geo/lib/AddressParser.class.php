<?php

/**
 *
 *
 * @author Aljosha Brell, tay
 * @since 14.11.2012
 *
 */
class AddressParser
{

    protected static $stopWords =
        array(
            '(arbeits|bildungs|fuß|geh|heim|lebens|schul|vor)weg',
            '(sponsor|ehe|müntefe)ring',
            'anstieg',
            'der',
            'die',
            'das',
            'den',
            'des',
            'dem',
            'eine?',
            'eine(r|s|m)',
            'gleich',
            'offener',
            'zu(r|m)',
            '(park|sport|stell|spiel|liege)platz',
            '(autobahn|fußgänger)brücke',
            '(getränke|super|elektrofach)markt'
        );

    protected static $_pregCities =
        array(
            0 => array(
                'alt tucheband',
                'barnim',
                'berlin',
                'bernau',
                'dahme',
                'elsterwerda',
                'falkensee',
                'frankfurt\s*(/(o\.?|oder)|\((o\.?|oder)\))',
                'grieben',
                'großräschen',
                'gumtow',
                'hamburg',
                'hammelspring',
                'königs wusterhausen',
                'meuro',
                'Oberhavel',
                'potsdam',
                'Premnitz',
                'schlieben',
                'Schwedt',
                'templin',
                'uckermark',
                'werben',
                'wildau',
                'Wittenberge',
                'Wittstock',
            ),
            1 => array(
                '(bee|wand)litz',
                '(wuste)witz',
                '(staak|rhin|telt|briesk|Rathen)ow(-\S+)?',
                '(fürsten|babels)berg',
                '(blanken|oranien)burg',
                '\S+münde',
                '\S+walde',
                '(nenn)hausen',
            #'([^\s]+)felde',
            #'([^\s]+)dorf',
            )
        );
    protected static $_pregStreets =
        array(
            0 => array(
                'adlergestell',
                'alt-\w+',
                'brandenburger\stor',
                'köllnische\sheide',
                'priesterstege',
                'siegmunds\shof',
                'straße\sdes\s17\.\sjuni',
                'trabrennbahn',
                'unter\den\eichen',
                // hamburg
                'hamburger\berg',
                'horner\srampe',
                'käkenflur',
                'reeperbahn',
            ),
            1 => array(
                '(straße|platz)\s+(der|des)\s+\w+',
                '\S+\s*str(asse|aße|\.?)',
                '\S+\s*ring',
                '\S+\s*allee',
                '\S+\s*damm',
                '\S+\s*chaussee',
                '\S+\s*gasse',
                '\S+\s*boulevard',
                '\S+\s*promenade',
                '\S+weg',
                '\S+steg',
                '\S+stieg',
                '\S+twiete',
                '\S+twete',
            ),
            2 => array(
                '\S+\s*pl(atz|\.)',
                '\S+\s*markt',
                '\S+\s*kamp',
                '\S+\s*ufer',
                '\S+h(e|a)ide',
                '\S+\s*deich',
                '\S+\s*plaza',
            ),
            3 => array(
                '(charlotten|rummels)burg',
                '(schöne|prenzlauer\s|lichten|kreuz)berg',
                '\w+dorf',
                '\w+horst',
                '\w+schöneweide',
                '\w+see',
                'adlershof',
                'dahlem',
                'dreilinden',
                'friedrichshain',
                'funkturm',
                'gesundbrunnen',
                'grünau',
                'hohenschönhausen',
                'johannisthal',
                'köpenick',
                'marzahn',
                'mitte',
                'moabit',
                'neukölln',
                'pankow',
                'spandau',
                'steglitz',
                'tegel',
                'tempelhof',
                'tiergarten',
                'treptow',
                'wedding',
                'weißensee',
                'westend',
                'zehlendorf',
                // hamburg
                'altstadt',
                'altstadt',
                'bahrenfeld',
                'bahrenfeld',
                'bergedorf',
                'bergstedt',
                'bramfeld',
                'eidelstedt',
                'fuhlsbüttel',
                'hamm',
                'harburg',
                'lokstedt',
                'marmstorf',
                'niendorf',
                'poppenbüttel',
                'reinbek',
                'sternschanze',
                'st\.\s*pauli',
                'volksdorf',
            ),
            4 => array(
                '\S+\s*park', '\S+\s*weg', '\S+\s*tunnel', '\S+\s*bahnhof', '\S+\s*brücke', '\S+\s*see',
            ),
        );


    protected static $_pregCitiesReplace =
        array(
            '/wunschstadtMatch/' => 'Wunschstadt', '/frankfurt\s*\(o.\)/' => 'Frankfurt (Oder)',
        );


    /**
     *
     *
     * @param string $description
     */
    public static function parse($description)
    {
        $result = array();
        $street = self::extractStreet($description);
        if ($street)
        {
            $house = self::extractHouse($description, $street);
            $postal = self::extractZip($description, $street);
            $city = self::extractCity($description, $street);
            return compact('street', 'house', 'postal', 'city');
        }
        else
        {
            return false;
        }
    }


    /**
     *
     *
     * @param string $description
     */
    public static function extractHouse($description, $street)
    {
        $result = array();
        if ($street)
        {
            $street = str_replace(')', '', $street);
            $street = str_replace('(', '', $street);
            preg_match_all('/' . preg_quote($street) . '(.{0,20})/sim', $description, $match);
            foreach ($match[1] as $extractedItem)
            {
                preg_match('/(\d[\d\w\-]?+\s*(\w\s)?)/sim', $extractedItem, $tmp);
                if ($tmp)
                    array_push($result, $tmp[0]);
            }
            if (!empty($result))
            {
                $result = array_unique($result);
                return trim($result[0]);
            }
        }
        return false;
    }


    /**
     * extract post_code
     *
     * @param string $description
     */
    public static function extractZip($description, $street)
    {
        $result = array();
        if ($street)
        {
            $street = str_replace(')', '', $street);
            $street = str_replace('(', '', $street);
            preg_match_all('/.{0,30}' . preg_quote($street) . '.{0,30}/sim', $description, $match);
            foreach ($match[0] as $extractedItem)
            {
                if (preg_match('/\d{5}/sim', $extractedItem, $tmp))
                {
                    return $tmp[0];
                }
            }
        }
        return false;
    }


    /**
     *
     *
     * @param string $description
     */
    public static function extractStreet($description)
    {
        $pregStopWords = '/^(' . join('|', self::$stopWords) . ')\b/uis';
        foreach (self::$_pregStreets as $precision => $regs)
        {
            $preg = '#\b(' . implode('|', $regs) . ')(\b|(?= )|$)#isu';
            if (preg_match_all($preg, $description, $matches, PREG_PATTERN_ORDER))
            {
                foreach ($matches[1] as $candidate)
                {
                    if (!preg_match($pregStopWords, $candidate))
                    {
                        return $candidate;
                    }
                }
            }
        }
    }


    /**
     * try to extract the city
     *
     * @param string $description
     * @param string $street found street {@see extractStreet()}
     * @return string
     */
    public static function extractCity($description, $street)
    {
        if (isset(self::$_pregCitiesReplace) && is_array(self::$_pregCitiesReplace) && count(self::$_pregCitiesReplace))
        {
            $haystack = array_keys(self::$_pregCitiesReplace);
            $needle = array_values(self::$_pregCitiesReplace);
            $description = preg_replace($haystack, $needle, $description);
        }

        if ($street)
        {
            $street = str_replace(')', '', $street);
            $street = str_replace('(', '', $street);
            preg_match_all('/.{0,40}' . preg_quote($street) . '.{0,40}/sim', $description, $match);
            foreach ($match[0] as $extractedItem)
            {
                foreach (self::$_pregCities as $precision => $regs)
                {
                    $preg = '#\b(' . implode('|', $regs) . ')(\b|(?= )|$)#ius';
                    if (preg_match($preg, $extractedItem, $m))
                    {
                        return $m[1];
                    }
                }
            }
        }

        return FALSE;
    }
}
