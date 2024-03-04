<?php

namespace App\Http\Services\Campaigns\Utils\Content;

use App\CampaignCreative;

final class CreativeContent
{
    protected $creativeIDs = [];

    protected $campaignCreatives = [];

    public $emptyContent = true;

    /**
     * Set creative ids
     *
     * @param  array  $creatives
     * @return void
     */
    public function setCreativeIDS($creatives)
    {
        $this->creativeIDs = $creatives;
        $this->setCampaignCreatives();
    }

    /**
     * Check if creative ids were set
     *
     * @return bolean
     */
    public function hasCreativeIDs()
    {
        return (count($this->creativeIDs)) ? true : false;
    }

    /**
     * Set the campaign creatives
     */
    public function setCampaignCreatives()
    {
        // Check if creative ids were set
        if ($this->hasCreativeIDs()) {
            // Query the campaign creatives data
            $this->campaignCreatives = CampaignCreative::whereIn('id', $this->creativeIDs)
                ->select('id', 'image', 'description')
                ->get()
                ->keyBy('id')
                ->toArray();
        }
    }

    /**
     * check if  creative id really exists
     *
     * @param  int  $campaignID
     * @return bolean
     */
    public function creativeIDExists($campaignID)
    {
        if ($this->campaignHasCreativeID($campaignID)) {
            return true;
        }

        return false;
    }

    /**
     * get creative ids
     *
     * @return array
     */
    public function creativeIDs()
    {
        return $this->creativeIDs;
    }

    /**
     * check campaign has creative id
     *
     * @param  int  $campaignID
     * @return bolean
     */
    public function campaignHasCreativeID($campaignID)
    {
        if (array_key_exists($campaignID, $this->creativeIDs)) {
            return true;
        }

        return false;
    }

    /**
     * get creative id of the campaign if available
     *
     * @param  int  $campaignID
     * @return int|void
     */
    public function campaignCreativeID($campaignID)
    {
        if ($this->campaignHasCreativeID($campaignID)) {
            return $this->creativeIDs[$campaignID];
        }

    }

    /**
     * Replace Stack Creative with Creative Content
     *
     * @param  string  $content
     * @param  int  $campaignID
     * @return string
     */
    public function get($content, $campaignID)
    {
        $html = '';
        if ($this->creativeIDExists($campaignID)) {
            $html .= $content;
            if ($html) {
                $this->emptyContent = false;
            }
            //Replace with creative description
            $html = $this->updateCreativeDescription($html, $campaignID);
            //Replace with creative image
            $html = $this->updateCreativeImage($html, $campaignID);
            //Replace with creative id and exit
            if ($this->haveCreativeID2Replace($html)) {
                return $this->updateCreativeID($html, $campaignID);
            }

            //If no creative id, add
            if (strpos($html, '<!-- Standard Required Data END-->') !== false) {
                $formArray = explode('<!-- Standard Required Data END-->', $html);
                $formArray[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$this->creativeIDs[$campaignID].'"/>';
                $formArray[0] .= '<!-- Standard Required Data END-->';
                $html = implode(' ', $formArray);

                return $html;
            }

            $formArray = explode('</form>', $html);
            $formArray[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$this->creativeIDs[$campaignID].'"/>';
            $formArray[0] .= '</form>';
            $html = implode(' ', $formArray);
        }

        return $html;
    }

    /**
     * Replace creative image
     *
     * @param  string  $html
     * @param  string  $description
     * @return string
     */
    protected function updateCreativeDescription($html, $campaignID)
    {
        if (strpos($html, '[VALUE_CREATIVE_DESCRIPTION]') !== false) {
            $creative = $this->campaignCreatives[$this->creativeIDs[$campaignID]];

            return str_replace('[VALUE_CREATIVE_DESCRIPTION]', $creative['description'], $html);
        }

        return $html;
    }

    /**
     * Replace creative description
     *
     * @param  string  $html
     * @param  string  $image
     * @return string
     */
    protected function updateCreativeImage($html, $campaignID)
    {
        if (strpos($html, '[VALUE_CREATIVE_IMAGE]') !== false) {
            $creative = $this->campaignCreatives[$this->creativeIDs[$campaignID]];

            return str_replace('[VALUE_CREATIVE_IMAGE]', $creative['image'], $html);
        }

        return $html;
    }

    /**
     * check if creative id is eplaceable
     *
     * @param  string  $html
     * @return bolean
     */
    protected function haveCreativeID2Replace($html)
    {
        if (strpos($html, '[VALUE_CREATIVE_ID]') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Replace creative id
     *
     * @param  string  $html
     * @param  string  $image
     * @return string
     */
    protected function updateCreativeID($html, $campaignID)
    {
        if (strpos($html, '[VALUE_CREATIVE_ID]') !== false) {
            return str_replace('[VALUE_CREATIVE_ID]', $this->creativeIDs[$campaignID], $html);
        }

        return $html;
    }
}
