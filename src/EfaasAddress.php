<?php

namespace Javaabu\EfaasSocialite;

use Illuminate\Support\Arr;

class EfaasAddress
{

    public string $AddressLine1;

    public string $AddressLine2;

    public string $Road;

    public string $AtollAbbreviation;

    public string $AtollAbbreviationDhivehi;

    public string $IslandName;

    public string $IslandNameDhivehi;

    public string $HomeNameDhivehi;

    public string $Ward;

    public string $WardAbbreviationEnglish;

    public string $WardAbbreviationDhivehi;

    public string $Country;

    public string $CountryISOThreeDigitCode;

    public string $CountryISOThreeLetterCode;

    public static function make($json_string): ?self
    {
        $decoded = json_decode($json_string, true);

        $new = new static;

        if (! is_array($decoded)) {
            return null;
        }

        foreach ($decoded as $key => $value) {
            $new->$key = $value;
        }

        return $new;
    }

    public function getFormattedAddress()
    {
        $address_string = '';

        if ($ward = $this->WardAbbreviationEnglish) {
            $address_string .= $ward . '. ';
        }

        if ($address_line_1 = $this->AddressLine1) {
            $address_string .= $address_line_1;
        }

        if ($address_line_2 = $this->AddressLine2) {
            $address_string .= ', ' . $address_line_2;
        }

        return $address_string;
    }

    public function getDhivehiFormattedAddress()
    {
        $address_string = '';

        if ($ward = $this->WardAbbreviationDhivehi) {
            $address_string .= $ward . '. ';
        }

        if ($address_line_1 = $this->HomeNameDhivehi) {
            $address_string .= $address_line_1;
        }

        return $address_string;
    }
}
