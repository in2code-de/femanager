.. include:: ../../Includes.txt

.. _countryselect:

Rate Limiter
------------

Basics
^^^^^^

If you want prevent, that your registration forms, you can enable the rate limiter.

Note: The limiter is enabled by default.


TypoScript Settings
^^^^^^^^^^^^^^^^^^^


.. code-block:: text

	plugin.tx_femanager {
		settings {
			ratelimiter {
			# Number of seconds for the sliding window rate limiter
			timeframe = {$plugin.tx_femanager.settings.ratelimiter.timeframe}
			# Request count. How many requests are allowed in the last <timeframe> seconds. Set to 0 to disable rate limiter.
			limit = {$plugin.tx_femanager.settings.ratelimiter.limit}
		}
		}
	}
