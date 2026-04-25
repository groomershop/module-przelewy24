define(function () {
    'use strict';

    return (P24TargetElementId) => {
        const P24TargetElement = document.getElementById(P24TargetElementId);
        if (P24TargetElement) {
            const iframe = P24TargetElement.querySelector('iframe');
            if (iframe) {
                iframe.remove();
            }
        }
    };
});
