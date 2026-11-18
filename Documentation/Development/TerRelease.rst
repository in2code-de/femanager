.. include:: ../Includes.rst.txt

.. _development-ter-release:

Release to TER
==============

Releases are never uploaded to the TER automatically. A maintainer uploads them with a DDEV command that checks the
release first and asks for a confirmation before the upload.

.. code-block:: bash

   ddev ter-release <tag> ["<release note>"] [--dry-run]

Requirements
------------

* The release preparations are committed: the version in :file:`ext_emconf.php` and the changelog.
* The commit is tagged with the version (e.g. ``13.4.2``), and the branch and the tag are pushed to the public
  repository https://github.com/in2code-de/femanager.
* You have a TER access token for the extension key ``femanager``.

Checks
------

The command stops with an error if one of these checks fails:

#. The tag is a version like ``13.4.2`` and exists locally.
#. The tag exists in the public repository and points to the same commit. Tags of the early access version, which
   exist only in the private repository, can therefore never be uploaded.
#. The version in :file:`ext_emconf.php` of the tagged commit is the same as the tag.
#. The version does not exist in the TER yet.

If no release note is given, the command asks for one. Pressing Enter takes the default
``Release Notes: https://github.com/in2code-de/femanager/releases/tag/<tag>``.

The command then builds the zip with ``git archive --prefix=femanager/`` in :file:`.Build/ter-release/`. The package
content is defined by the ``export-ignore`` entries in :file:`.gitattributes`. Before the upload it shows the version,
the commit, the release note and the number of files, and asks ``Upload to TER? [y/N]``.

Dry run
-------

With ``--dry-run`` the command runs all checks and builds the zip, but does not upload anything. The zip stays in
:file:`.Build/ter-release/` for inspection.

.. code-block:: bash

   ddev ter-release 13.4.2 --dry-run

Access token
------------

The command reads the token from the environment variable ``TYPO3_API_TOKEN`` or asks for it. To store the token for
your own user only, outside of the project:

.. code-block:: bash

   ddev config global --web-environment-add="TYPO3_API_TOKEN=<token>"

Never commit the token, for example to :file:`.env` or :file:`.ddev/config.yaml`.

Release workflow
----------------

#. Commit the release preparations (version in :file:`ext_emconf.php`, changelog).
#. Tag the commit and push the branch and the tag to the public repository, one tag at a time.
#. Check the release: ``ddev ter-release 13.4.2 --dry-run``
#. Upload the release: ``ddev ter-release 13.4.2`` and confirm with ``y``.

A version that is in the TER cannot be uploaded again, and a pushed tag must not be moved. If something is wrong,
release a new version.
