<?php
namespace Pulq\Agavi\Filter;
use \AgaviFilter;
use \AgaviIGlobalFilter;
use \AgaviIActionFilter;
use \AgaviFilterChain;
use \AgaviExecutionContainer;

/**
 * When relying on the FormPopulationFilter and ESI includes in the same application,
 * the FPF will change all the response content into XML DOM, translating <esi:include />
 * into <include />.
 * Use this filter to restore the right ESI tag and the correct functionality.
 *
 * This filter must run AFTER the FPF
 */
class FixEsiFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
    /**
     * Execute this filter.
     *
     * @param      AgaviFilterChain        The filter chain.
     * @param      AgaviExecutionContainer The current execution container.
     *
     * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
     */
    public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
    {
        $filterChain->execute($container);
        $response = $container->getResponse();
        $output_types = $this->getParameter('output_types', array('html'));
        
        if ($response->isContentMutable() && in_array($response->getOutputType()->getName(), $output_types))
        {
            $str = preg_replace(
                    '#<include\s+src="(.*?)"></include>#s',
                    '<esi:include src="$1"/>',
                    $response->getContent());
            $response->setContent($str);
        }
    }
}
