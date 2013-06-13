<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base Behat event.
 *
 * @author Alexander Shvets <neochief@shvetsgroup.com>
 */
class BehatEvent extends Event implements EventInterface, \Serializable
{
    /**
     * Serialize default Event properties.
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'name' => $this->getName(),
                'propagationStopped' => $this->isPropagationStopped(),
            )
        );
    }

    /**
     * Unserialize default Event properties.
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->setName($data['name']);
        if ($data['propagationStopped']) {
            $this->stopPropagation();
        }
    }
}
