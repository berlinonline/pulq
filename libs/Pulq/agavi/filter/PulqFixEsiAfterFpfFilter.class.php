<?php

/**
 * Fix ESI includes if FormPopulationFilter was used
 *
 *
 * @author     Tom Anheyer <tom.anheyer@BerlinOnline.de>
 */
class PulqFixEsiAfterFpfFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
    /**
     * Fix ESI includes if FormPopulationFilter was used
     *
     * @param      AgaviFilterChain        The filter chain.
     * @param      AgaviExecutionContainer The current execution container.
     */
    public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
    {
        $filterChain->execute($container);
        $response = $container->getResponse();

        $fpf =
            $this->getContext()
                ->getRequest()
                ->getAttribute('populate', 'org.agavi.filter.FormPopulationFilter');
        if ($fpf && $response->isContentMutable() && "html" == $response->getOutputType()
                    ->getName())
        {
            $str =
                preg_replace('#<include\s+xmlns:esi=".*?"\s+src="(.*?)"></include>#s', '<esi:include src="$1"/>',
                    $response->getContent());
            $response->setContent($str);
        }
    }

}

