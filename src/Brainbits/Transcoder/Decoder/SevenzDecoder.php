<?php
/**
 * This file is part of the brainbits transcoder package.
 *
 * (c) 2012-2013 brainbits GmbH (http://www.brainbits.net)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brainbits\Transcoder\Decoder;

use Assert\Assertion as Assert;

/**
 * 7z decoder
 *
 * @author Gregor Welters <gwelters@brainbits.net>
 */
class SevenzDecoder implements DecoderInterface
{
    const TYPE = '7z';

    /**
     * @var string
     */
    private $executable;

    /**
     * @param string $executable
     */
    public function __construct($executable = '7z')
    {
        $this->executable = $executable;
    }

    /**
     * Return executable
     *
     * @return string
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * @inheritDoc
     */
    public function decode($data)
    {

        $command = escapeshellarg($this->executable) . ' e -an -txz -m0=lzma2 -mx=9 -mfb=64 -md=32m -si -so';
        $process = proc_open($command, [ ['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w'] ], $pipes, null, null);

        if (strlen($data)) {
            $exitCode = 0;
            $errors = '';

            if (is_resource($process)) {
                fwrite($pipes[0], $data);
                fclose($pipes[0]);
                $data = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                $exitCode = proc_close($process);
            }

            Assert::minLength($data, 1, '7z decoder returned no data, exit code ' . $exitCode . ', error output ' . $errors);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
