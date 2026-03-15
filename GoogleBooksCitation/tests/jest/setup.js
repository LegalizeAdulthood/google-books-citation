// SPDX-License-Identifier: GPL-2.0-only

/**
 * Setup mocks for MediaWiki environment
 */

// Mock MediaWiki API
global.mw = {
    msg: jest.fn((key) => {
        const messages = {
            'googlebooks-citation-toolbar-button': 'Google Books citation',
            'googlebooks-citation-toolbar-prompt': 'Enter a Google Books URL:',
            'googlebooks-citation-error-fetch': 'GoogleBooksCitation error: could not fetch the Google Books page.'
        };
        return messages[key] || key;
    }),

    notify: jest.fn(),

    Api: jest.fn().mockImplementation(() => ({
        get: jest.fn()
    })),

    hook: jest.fn().mockImplementation(() => ({
        add: jest.fn()
    })),

    loader: {
        using: jest.fn((modules, callback) => {
            if (typeof callback === 'function') {
                callback();
            }
            return Promise.resolve();
        }),
        getState: jest.fn(() => 'ready')
    },

    config: {
        get: jest.fn((key) => {
            const config = {
                'wgAction': 'edit',
                'wgNamespaceNumber': 0
            };
            return config[key];
        })
    },

    util: {
        addPortletLink: jest.fn()
    }
};

// Mock mediaWiki global alias
global.mediaWiki = global.mw;

// Mock jQuery
global.$ = jest.fn((selector) => {
    const element = {
        on: jest.fn().mockReturnThis(),
        wikiEditor: jest.fn().mockReturnThis(),
        textSelection: jest.fn().mockReturnThis(),
        text: jest.fn().mockReturnThis()
    };
    return element;
});

global.jQuery = global.$;

global.$.inArray = jest.fn((value, array) => array.indexOf(value));
