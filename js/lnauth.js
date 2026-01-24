(function(Drupal, drupalSettings, once) {
  "use strict";

  const ns = "lnauth";

  Drupal.behaviors[ns] = {
    attach(context) {
      const settings = drupalSettings[ns];
      if (!settings) return;

      // Use <body> as a stable place to host once() anchors.
      const body = document.body;

      Object.keys(settings).forEach(delta => {
        const instance = settings[delta] || {};
        const k1 = instance.k1;
        const url = instance.url;

        // frequency in ms; default 1000 if missing/invalid.
        const frequency =
          Number(instance.frequency) > 0 ? Number(instance.frequency) : 1000;

        // attempts: 0 => unlimited; otherwise positive integer.
        const attempts = Number.isFinite(Number(instance.attempts))
          ? Number(instance.attempts)
          : 0;

        if (!k1 || !url) return;

        // Create a unique hidden anchor per k1 so once() can prevent duplicates.
        const anchorId = `${ns}-${k1}`;
        let anchor = document.getElementById(anchorId);
        if (!anchor) {
          anchor = document.createElement("span");
          anchor.id = anchorId;
          anchor.hidden = true;
          body.appendChild(anchor);
        }

        once(`${ns}:${k1}`, anchor).forEach(() => {
          let attempt = 0;

          const interval = window.setInterval(async () => {
            // Stop if we've reached the max attempt count (when attempts != 0).
            if (attempts !== 0 && attempt >= attempts) {
              window.clearInterval(interval);
              return;
            }

            attempt += 1;

            try {
              const res = await fetch(url, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                  Accept: "application/json"
                }
              });

              if (!res.ok) return;

              // If the endpoint sometimes returns non-JSON, this will throw and be caught.
              const data = await res.json();

              if (data && data.authenticated === true) {
                window.clearInterval(interval);
                window.location.reload();
              }
            } catch (e) {
              // Optional: add console.debug(e);
            }
          }, frequency);
        });
      });
    }
  };
})(Drupal, drupalSettings, once);
