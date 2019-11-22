<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Class CurrentDateTimeHelper.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
class CurrentDateTimeHelper implements DateTimeHelper
{
    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): int
    {
        return \time();
    }
}
