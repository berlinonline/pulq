<?php

/**
 * The base view from which all Localnews module views inherit.
 */
class ProjectLocalnewsBaseView extends ProjectBaseView
{
    public function setupHtml(AgaviRequestDataHolder $rd, $layout = 'localnews')
    {
        parent::setupHtml($rd, $layout);
    }
}
