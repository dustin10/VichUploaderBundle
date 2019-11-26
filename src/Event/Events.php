<?php

namespace Vich\UploaderBundle\Event;

/**
 * Contains all the events triggered by the bundle.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class Events
{
    /**
     * Triggered before a file upload is handled.
     *
     * @note This event is the same for new and old entities.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const PRE_UPLOAD = 'vich_uploader.pre_upload';

    /**
     * Triggered right after a file upload is handled.
     *
     * @note This event is the same for new and old entities.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const POST_UPLOAD = 'vich_uploader.post_upload';

    /**
     * Triggered before a file is injected into an entity.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const PRE_INJECT = 'vich_uploader.pre_inject';

    /**
     * Triggered after a file is injected into an entity.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const POST_INJECT = 'vich_uploader.post_inject';

    /**
     * Triggered before a file is removed.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const PRE_REMOVE = 'vich_uploader.pre_remove';

    /**
     * Triggered after a file is removed.
     *
     * @Event("Vich\UploaderBundle\Event\Event")
     */
    public const POST_REMOVE = 'vich_uploader.post_remove';
}
