# NOTE
# Run this 'eval' command at the command line to define the helper
# function for the current terminal:
#     eval "$(docker run --rm t3docs/render-documentation:v3.1.0 show-shell-commands)"
#
# As alternative, you can 'source' the code directly into the shell:
#     source <(docker run --rm t3docs/render-documentation:v3.1.0 show-shell-commands)
# ATTENTION:
#     No whitespace between '<('
#
# Or, use an intermediate file:
#     docker run --rm t3docs/render-documentation:v3.1.0 show-shell-commands >shell-commands.sh
#     source shell-commands.sh

# This function defines the helper function, usually named \'dockrun_t3rd\'
function dockrun_t3rd () {

# Environment variables the USER may find important (on the host!),
# no slash ('/') at the end,
# default is the current directory $(pwd):
#
#     T3DOCS_PROJECT=/abspathto/MyProjectStartFolder       (readonly)
#     T3DOCS_RESULT=/abspathto/ResultFolder                (readwrite)
#     T3DOCS_TMP=/abspathto/TemporaryFolder                (readwrite)
#     T3DOCS_THEMES=/abspathto/MySphinxThemes              (readonly)
#
# Environment variables only some DEVELOPERS may find important,
# no slash ('/') at the end,
#
#     T3DOCS_MAKEDIR=/abspathto/MYALL/Makedir
#     T3DOCS_MENU=/abspathto/MYALL/Menu
#     T3DOCS_TOOLCHAINS=/abspathto/MYALL/Toolchains
#     T3DOCS_USERHOME=/abspathto/MYALL/userhome
#     T3DOCS_VENV=/abspathto/MYALL/venv
#     T3DOCS_WHEELS=/abspathto/WheelsFolder
#     T3DOCS_DEBUG=0         (0 or 1, talk to stdout)
#     T3DOCS_DRY_RUN=0       (0 or 1, don't really execute)
#     T3DOCS_GIT_RESTORE_MTIME=1  (0 or 1, usually 1=on)

local DEBUG=${T3DOCS_DEBUG:-0}
local GIT_RESTORE_MTIME=1
local DRY_RUN=${T3DOCS_DRY_RUN:-0}
local git_restore_mtime=$(which git-restore-mtime)
local exitcode=$?
if [[ $exitcode -ne 0 ]] || [[ "$GIT_RESTORE_MTIME" = "0" ]]; then git_restore_mtime=; fi

# start command building
local cmd="docker run --rm"
if [[ $# -eq 0 ]]; then
   # create folders on host? (e.g. Documentation-GENERATED-temp)
   local CREATING=1
   cmd="$cmd --user=$(id -u):$(id -g)"
elif [[ "$@" = "/bin/bash" ]]; then
   local CREATING=1
   cmd="$cmd --entrypoint /bin/bash -it"
elif [[ "$@" = "/usr/bin/bash" ]]; then
   local CREATING=1
   cmd="$cmd --user=$(id -u):$(id -g)"
   cmd="$cmd --entrypoint /bin/bash -it"
elif [[ "$1" = "serve4build" ]]; then
   local CREATING=1
   local PORT=${2:-9999}
   cmd="$cmd --user=$(id -u):$(id -g) -it"
   cmd="$cmd --publish=${PORT}:${PORT}/tcp"
elif [[ "$1" = "just1sphinxbuild" ]]; then
   local CREATING=1
   cmd="$cmd --user=$(id -u):$(id -g)"
elif [[ "$@" = "export-ALL" ]]; then
   local CREATING=1
   cmd="$cmd --entrypoint /bin/bash"
else
   local CREATING=1
   cmd="$cmd --user=$(id -u):$(id -g)"
fi

# PROJECT - read only!
# assume existing folder PROJECT/Documentation
# absolute path to existing folder PROJECT or current dir
local PROJECT=${T3DOCS_PROJECT:-$(pwd)}
cmd="$cmd -v $PROJECT:/PROJECT:ro"
if (($DEBUG)); then echo "PROJECT......: $PROJECT"; fi

# RESULT
# absolute path to existing folder RESULT of (RESULT/Documentation-GENERATED-temp)
local RESULT=${T3DOCS_RESULT:-$(pwd)}
# force special name to prevent create/delete disasters
RESULT=${RESULT}/Documentation-GENERATED-temp
cmd="$cmd -v $RESULT:/RESULT"
if (($CREATING)); then
   if (($DEBUG)); then echo creating.....: mkdir -p "$RESULT" ; fi
   mkdir -p "$RESULT" 2>/dev/null
fi
if (($DEBUG)); then echo "git_restore_mtime: $git_restore_mtime"; fi
if (($DEBUG)); then echo "RESULT.......: $RESULT"; fi
# TMP
# absolute path to existing folder TMP of (TMP/tmp-GENERATED-temp)
local TMP=${T3DOCS_TMP:-$(pwd)}
# force special name to prevent create/delete disasters
TMP=${TMP}/tmp-GENERATED-temp
if (($CREATING)) && [[ -n "${T3DOCS_TMP}" ]]; then
   mkdir -p "$TMP" 2>/dev/null
fi
if [[ -d "${TMP}" ]]; then
   cmd="$cmd -v $TMP:/tmp"
   if (($CREATING)); then
      /bin/bash -c "rm -rf $TMP/*"
   fi
   if (($DEBUG)); then echo "TMP..........: $TMP"; fi
fi

# USERHOME
# absolute path to existing folder 'userhome'
local USERHOME=${T3DOCS_USERHOME:-$(pwd)/tmp-GENERATED-userhome}
if [ -d "$USERHOME" ]; then
   cmd="$cmd -v $USERHOME:/ALL/userhome"
   if (($DEBUG)); then echo "USERHOME......: $USERHOME"; fi
fi

# MAKEDIR
# absolute path to existing folder MAKEDIR
local MAKEDIR=${T3DOCS_MAKEDIR:-$(pwd)/tmp-GENERATED-Makedir}
if [ -d "$MAKEDIR" ]; then
   cmd="$cmd -v $MAKEDIR:/ALL/Makedir"
   if (($DEBUG)); then echo "MAKEDIR......: $MAKEDIR"; fi
fi

# MENU
# absolute path to existing folder MENU
local MENU=${T3DOCS_MENU:-$(pwd)/tmp-GENERATED-Menu}
if [ -d "$MENU" ]; then
   cmd="$cmd -v $MENU:/ALL/Menu"
   if (($DEBUG)); then echo "MENU.........: $MENU"; fi
fi

# VENV (used to be 'Rundir')
# absolute path to existing folder VENV
local VENV=${T3DOCS_VENV:-$(pwd)/tmp-GENERATED-venv}
if [ -d "$VENV" ]; then
   cmd="$cmd -v $VENV:/ALL/venv"
   if (($DEBUG)); then echo "VENV.........: $VENV"; fi
fi

# THEMES
# absolute path to a folder containing Sphinx themes
local THEMES=${T3DOCS_THEMES:-$(pwd)/tmp-GENERATED-Themes}
if [ -d "$THEMES" ]; then
   cmd="$cmd -v $THEMES:/THEMES"
   if (($DEBUG)); then echo "THEMES.......: $THEMES"; fi
fi

# TOOLCHAINS
# absolute path to existing folder TOOLCHAINS
local TOOLCHAINS=${T3DOCS_TOOLCHAINS:-$(pwd)/tmp-GENERATED-Toolchains}
if [ -d "$TOOLCHAINS" ]; then
   cmd="$cmd -v $TOOLCHAINS:/ALL/Toolchains"
   if (($DEBUG)); then echo "TOOLCHAINS...: $TOOLCHAINS"; fi
fi

# WHEELS
# absolute path to a folder containing Python wheel packages
local WHEELS=${T3DOCS_WHEELS:-$(pwd)/tmp-GENERATED-Wheels}
if [ -d "$WHEELS" ]; then
   cmd="$cmd -v $WHEELS:/WHEELS"
   if (($DEBUG)); then echo "WHEELS.......: $WHEELS"; fi
fi

cmd="$cmd ghcr.io/t3docs/render-documentation:v3.1.0"
if (($DEBUG)); then echo "OUR_IMAGE....: t3docs/render-documentation:v3.1.0"; fi

# add remaining arguments
if [[ "$@" = "/bin/bash" ]]; then
   true "do nothing here"
elif [[ "$@" = "/usr/bin/bash" ]]; then
   true "do nothing here"
elif [[ "$1" = "serve4build" ]]; then
   cmd="$cmd ${1} ${PORT}"
elif [[ "$1" = "just1sphinxbuild" ]]; then
   cmd="$cmd $@"
   echo "See 'Documentation-GENERATED-temp/sphinx-build' for build information"
elif [[ "$@" = "export-ALL" ]]; then
   cmd="$cmd -c \"rsync -a --delete --chown=$(id -u):$(id -g) /ALL/ /RESULT/ALL-exported\""
   echo The export will go to:
   echo "   $RESULT/ALL-exported"

else
   cmd="$cmd $@"
   # if script git-restore-mtime exists and '*make*' in args try the command
   # See README: get 'git-restore-mtime' from https://github.com/MestreLion/git-tools
   if [[ "$git_restore_mtime" != "" ]] && [[ $@ =~ .*make.* ]]; then
      if (($DEBUG)); then
         echo $git_restore_mtime
         $git_restore_mtime
      else
         $git_restore_mtime 2>/dev/null
      fi
   fi
fi
if [[ -w "$RESULT" ]]; then true
   echo "$cmd" | sed "s/-v /\\\\\\n   -v /g" >"$RESULT/last-docker-run-command-GENERATED.sh.txt"
fi
if (($DEBUG || $DRY_RUN)); then
   echo $cmd | sed "s/-v /\\\\\\n   -v /g"
fi
if [[ "$DRY_RUN" = "0" ]]; then
   eval "$cmd"
fi
}

if [[ ! -v DOCKRUN_FN_QUIET ]]; then true
   DOCKRUN_FN_QUIET=0
fi
if [[ "$DOCKRUN_FN_QUIET" -ne 1 ]]; then true
   echo "This function is now defined FOR THIS terminal window to run 'v3.1.0':"
   echo "    dockrun_v3.1.0"
   echo ""
   echo "(Define  'export DOCKRUN_FN_QUIET=1'  to turn this message off)"
fi

