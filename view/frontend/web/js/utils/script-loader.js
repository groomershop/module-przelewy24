define(function () {
    'use strict';

    return (src) => {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.type = 'text/javascript';
            script.async = true;

            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));

            document.body.appendChild(script);
        });
    };
});
