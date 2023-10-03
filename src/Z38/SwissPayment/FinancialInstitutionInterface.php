<?php

namespace Z38\SwissPayment;

use DOMDocument;
use DOMElement;

/**
 * General interface for financial institutions
 */
interface FinancialInstitutionInterface
{
    /**
     * Returns an XML representation to identify the financial institution
     *
     * @param DOMDocument $doc
     * @param string $spsVersion
     * @return DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc, string $spsVersion);
}
