<?php

class BerlinOnline_Sniffs_WhiteSpace_ScopeIndentSniff extends Generic_Sniffs_WhiteSpace_ScopeIndentSniff
{
    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * Does the indent need to be exactly right.
     *
     * If TRUE, indent needs to be exactly $ident spaces. If FALSE,
     * indent needs to be at least $ident spaces (but can be more).
     *
     * @var bool
     */
    public $exact = false;
    
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // If this is an inline condition (ie. there is no scope opener), then
        // return, as this is not a new scope.
        if (isset($tokens[$stackPtr]['scope_opener']) === false)
        {
            return;
        }

        if ($tokens[$stackPtr]['code'] === T_ELSE)
        {
            $next = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true
            );

            // We will handle the T_IF token in another call to process.
            if ($tokens[$next]['code'] === T_IF)
            {
                return;
            }
        }

        // Find the first token on this line.
        $firstToken = $stackPtr;

        for ($i = $stackPtr; $i >= 0; $i--)
        {
            // Record the first code token on the line.
            if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$emptyTokens) === false)
            {
                $firstToken = $i;
            }

            // It's the start of the line, so we've found our first php token.
            if ($tokens[$i]['column'] === 1)
            {
                break;
            }
        }

        // Based on the conditions that surround this token, determine the
        // indent that we expect this current content to be.
        $expectedIndent = $this->calculateExpectedIndent($tokens, $firstToken);

        if ($tokens[$firstToken]['column'] !== $expectedIndent)
        {
            $error = 'Line indented incorrectly; expected %s spaces, found %s';
            $data = array(
                ($expectedIndent - 1),
                ($tokens[$firstToken]['column'] - 1),
            );
            $phpcsFile->addError($error, $stackPtr, 'Incorrect', $data);
        }

        $scopeOpener = $tokens[$stackPtr]['scope_opener'];
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];

        // Some scopes are expected not to have indents.
        if (in_array($tokens[$firstToken]['code'], $this->nonIndentingScopes) === false)
        {
            $indent = ($expectedIndent + $this->indent);
        }
        else
        {
            $indent = $expectedIndent;
        }

        $newline = false;
        $comments = array(T_COMMENT, T_DOC_COMMENT, T_HEREDOC, T_END_HEREDOC);

        // Only loop over the content beween the opening and closing brace, not
        // the braces themselves.
        for ($i = ($scopeOpener + 1); $i < $scopeCloser; $i++)
        {

            // If this token is another scope, skip it as it will be handled by
            // another call to this sniff.
            if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$scopeOpeners) === true)
            {
                if (isset($tokens[$i]['scope_opener']) === true)
                {
                    $i = $tokens[$i]['scope_closer'];

                    // If the scope closer is followed by a semi-colon, the semi-colon is part
                    // of the closer and should also be ignored. This most commonly happens with
                    // CASE statements that end with "break;", where we don't want to stop
                    // ignoring at the break, but rather at the semi-colon.
                    $nextToken = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($i + 1), null, true);
                    if ($tokens[$nextToken]['code'] === T_SEMICOLON)
                    {
                        $i = $nextToken;
                    }
                }
                else
                {
                    // If this token does not have a scope_opener indice, then
                    // it's probably an inline scope, so let's skip to the next
                    // semicolon. Inline scopes include inline if's, abstract
                    // methods etc.
                    $nextToken = $phpcsFile->findNext(T_SEMICOLON, $i, $scopeCloser);
                    if ($nextToken !== false)
                    {
                        $i = $nextToken;
                    }
                }

                continue;
            }

            // Ignore all comments.
            if (in_array($tokens[$i]['code'], $comments))
            {
                continue;
            }

            if ($tokens[$i]['column'] === 1)
            {
                // We started a newline.
                $newline = true;
            }

            if ($newline === true && $tokens[$i]['code'] !== T_WHITESPACE)
            {
                // If we started a newline and we find a token that is not
                // whitespace, then this must be the first token on the line that
                // must be indented.
                $newline = false;
                $firstToken = $i;

                $column = $tokens[$firstToken]['column'];

                // Special case for non-PHP code.
                if ($tokens[$firstToken]['code'] === T_INLINE_HTML)
                {
                    $trimmedContentLength
                        = strlen(ltrim($tokens[$firstToken]['content']));
                    if ($trimmedContentLength === 0)
                    {
                        continue;
                    }

                    $contentLength = strlen($tokens[$firstToken]['content']);
                    $column = ($contentLength - $trimmedContentLength + 1);
                }

                // Check to see if this constant string spans multiple lines.
                // If so, then make sure that the strings on lines other than the
                // first line are indented appropriately, based on their whitespace.
                if (in_array($tokens[$firstToken]['code'], PHP_CodeSniffer_Tokens::$stringTokens) === true)
                {
                    if (in_array($tokens[($firstToken - 1)]['code'], PHP_CodeSniffer_Tokens::$stringTokens) === true)
                    {
                        // If we find a string that directly follows another string
                        // then its just a string that spans multiple lines, so we
                        // don't need to check for indenting.
                        continue;
                    }
                }

                // The token at the start of the line, needs to have its' column
                // greater than the relative indent we set above. If it is less,
                // an error should be shown.
                if ($column !== $indent)
                {
                    if ($this->exact === true || $column < $indent)
                    {
                        $type = 'IncorrectExact';
                        $error = 'Line indented incorrectly; expected ';
                        if ($this->exact === false)
                        {
                            $error .= 'at least ';
                            $type = 'Incorrect';
                        }

                        $error .= '%s spaces, found %s';
                        $data = array(
                            ($indent - 1),
                            ($column - 1),
                        );
                        $phpcsFile->addError($error, $firstToken, $type, $data);
                    }
                }
            }
        }
    }
}

?>
