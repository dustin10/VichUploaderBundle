<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Interface DateTimeHelper.
 *
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
interface DateTimeHelper
{
    /**
     * @return int
     */
    public function getTimestamp(): int;
}
