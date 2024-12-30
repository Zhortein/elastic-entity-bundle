<?php

namespace Zhortein\ElasticEntityBundle\Exception;

use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationException extends \RuntimeException
{
    public function __construct(TranslatorInterface $translator, string $messageKey, array $parameters = [])
    {
        $message = $translator->trans($messageKey, $parameters, 'zhortein_elastic_entity-validation');
        parent::__construct($message);
    }
}
