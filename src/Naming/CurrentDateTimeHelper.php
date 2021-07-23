<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Class CurrentDateTimeHelper.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 * @final
 */
class CurrentDateTimeHelper implements DateTimeHelper
{
    public function getTimestamp(): int
    {
        return \time();
    }
}
