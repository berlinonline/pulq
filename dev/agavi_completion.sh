#/bin/sh

_bin_agavi_magic() 
{
  local cur opts
  COMPREPLY=()
  cur="${COMP_WORDS[COMP_CWORD]}" 
  opts=$( cat /dev/null | bin/agavi -l | perl -ne '/^\s+(\w\S+)/ && print "$1\n"' )
  COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )

  return 0
}

complete -F _bin_agavi_magic agavi 

