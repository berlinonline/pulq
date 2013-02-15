<?php
/**
 * Convenience phing task that can that provides control over executing shell commands,
 * therby providing support for:
 * - hiding passwords
 * - supressing output
 *
 * @author Thorsten Schmitt-Rink
 */
class HoneybeeRunTask extends Task
{
    private $dir;
    private $command;
    private $revealPasswords = false;
    private $supressEcho = false;

    function setCommand($command)
    {
        $this->command = $command;
    }

    function setDir($dir)
    {
        $this->dir = $dir;
    }

    function setSupressEcho($supress)
    {
        $this->supressEcho = $supress;
    }

    function setRevealPasswords($reveal_passwords)
    {
        $this->revealPasswords = $reveal_passwords;
    }

    function main()
    {
        $this->log("Changing to " . $this->dir);

        if (!chdir($this->dir))
        {
            $this->log('FAILED to change into given directory "' . $this->dir . '. ABORTING...', Project::MSG_ERR);
        }
        else
        {
            $echo_cmd = $this->command;

            if (true !== $this->revealPasswords)
            {
                $echo_cmd = preg_replace('~--password\s+\S+~is', '', $this->command);
            }

            if (!$this->supressEcho)
            {
                $this->log("Execution " . $echo_cmd);
            }

            system($this->command);
        }
    }
}
