// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

import Ajax from 'core/ajax';

/**
 * Refresh the lightweight teacher dashboard counters.
 *
 * @param {number} cmid Course module id.
 */
export const init = (cmid) => {
    const dashboard = document.querySelector('[data-checkpoint-dashboard]');
    if (!dashboard) {
        return;
    }

    const refresh = async() => {
        const [data] = await Ajax.call([{
            methodname: 'mod_checkpoint_get_status',
            args: {cmid},
        }]);

        for (const key of ['submitted', 'graded', 'reopened', 'late']) {
            const target = dashboard.querySelector(`[data-checkpoint-${key}]`);
            if (target) {
                target.textContent = data[key];
            }
        }
    };

    refresh().catch(() => {
        // The server remains the source of truth; a failed refresh should not replace the rendered values.
    });
};
