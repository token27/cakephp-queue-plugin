<?php

declare(strict_types=1);

namespace Token27\Queue\Transport;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use InvalidArgumentException;
#
use Token27\Queue\Transport\JobInterface;

abstract class Job implements JobInterface {

    /**
     * Before send callback.
     *
     * @var array
     */
    protected $_beforeSendCallback = [];

    /**
     * After send callback.
     *
     * @var array
     */
    protected $_afterSendCallback = [];

    /**
     * Queue options
     *
     * @var array
     */
    protected $_queueOptions = [];

    /**
     * Locale string
     *
     * @var string
     */
    protected $_locale = null;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct() {
        if (Configure::read('Queue.defaultLocale') === null) {
            throw new InvalidArgumentException("Queue.defaultLocale is not configured");
        }
        $this->_locale = Configure::read('Queue.defaultLocale');
//        if (Configure::check('Queue.queueOptions') && is_array(Configure::read('Queue.queueOptions'))) {
//            $this->_queueOptions = Configure::read('Queue.queueOptions');
//        }
    }

    /**
     * Push the Notification into the queue
     *
     * @return bool
     */
    abstract public function push(): bool;

    /**
     * Do the job immediately
     *
     * @param string|array|null $content String with message or array with messages
     * @return \Queue\Trasnport\QueueInterface
     */
    abstract public function send($content = null): JobInterface;

    /**
     * Push the Notification into the queue
     *
     * @return bool
     */
    abstract public function run(): void;

//    /**
//     * Schedule the job into for later
//     *
//     * @param string|array|null $content String with message or array with messages
//     * @return \Queue\Trasnport\QueueInterface
//     */
//    abstract public function schedule($content = null): QueueInterface;

    /**
     * {@inheritdoc}
     */
    public function getBeforeSendCallback(): array {
        return $this->_beforeSendCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function setBeforeSendCallback($class = null, array $args = []): JobInterface {
        return $this->__setCallback('_beforeSendCallback', $class, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function addBeforeSendCallback($class, array $args = []) {
        return $this->__addCallback('_beforeSendCallback', $class, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getAfterSendCallback(): array {
        return $this->_afterSendCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function setAfterSendCallback($class = null, array $args = []): JobInterface {
        return $this->__setCallback('_afterSendCallback', $class, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function addAfterSendCallback($class, array $args = []) {
        return $this->__addCallback('_afterSendCallback', $class, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueOptions(): ?array {
        return $this->_queueOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueueOptions(array $options = null): JobInterface {
        return $this->__setQueueOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): ?string {
        return $this->_locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale = null): JobInterface {
        return $this->__setLocale($locale);
    }

    /**
     * Set locale
     *
     * @param string $locale locale - must be i18n conform
     * @return $this
     */
    private function __setLocale(string $locale): JobInterface {
        $this->_locale = $locale;

        return $this;
    }

    /**
     * Set settings
     *
     * @param array $options Queue options
     * @return $this
     */
    private function __setQueueOptions(array $options): JobInterface {
        $this->_queueOptions = Hash::merge($this->_queueOptions, $options);
        return $this;
    }

    /**
     * Set callback
     *
     * @param string $type _beforeSendCallback or _afterSendCallback
     * @param string|array $class name of the class
     * @param array $args array of arguments
     * @return $this
     */
    private function __setCallback(string $type, $class, array $args): JobInterface {
        if (!is_array($class)) {
            $this->{$type} = [
                [
                    'class' => $class,
                    'args' => $args,
                ],
            ];

            return $this;
        } elseif (is_array($class) && count($class) == 2) {
            $className = $class[0];
            $methodName = $class[1];
        } else {
            if (is_array($class)) {
                $class = implode($class);
            }
            throw new InvalidArgumentException("{$class} is misformated");
        }

        $this->{$type} = [
            [
                'class' => [$className, $methodName],
                'args' => $args,
            ],
        ];

        return $this;
    }

    /**
     * Add callback
     *
     * @param string $type _beforeSendCallback or _afterSendCallback
     * @param string|array $class name of the class
     * @param array $args array of arguments
     * @return $this
     */
    private function __addCallback(string $type, $class, array $args): JobInterface {
        if (!is_array($class)) {
            $this->{$type}[] = [
                'class' => $class,
                'args' => $args,
            ];

            return $this;
        } elseif (is_array($class) && count($class) == 2) {
            $className = $class[0];
            $methodName = $class[1];
        } else {
            if (is_array($class)) {
                $class = implode($class);
            }
            throw new InvalidArgumentException("{$class} is misformated");
        }

        $this->{$type}[] = [
            'class' => [$className, $methodName],
            'args' => $args,
        ];

        return $this;
    }

}
