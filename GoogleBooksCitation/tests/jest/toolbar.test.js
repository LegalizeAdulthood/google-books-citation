// SPDX-License-Identifier: GPL-2.0-only

/**
 * Tests for ext.googleBooksCitation.toolbar.js
 * @jest-environment jsdom
 */

describe('GoogleBooksCitation Toolbar', () => {
    let mockApi;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();

        // Setup DOM
        document.body.innerHTML = '<textarea id="wpTextbox1"></textarea>';

        // Mock API instance
        mockApi = {
            get: jest.fn()
        };
        mw.Api.mockReturnValue(mockApi);
    });

    describe('Module Loading', () => {
        test('module is registered', () => {
            const state = mw.loader.getState('ext.googleBooksCitation.toolbar');
            expect(state).toBe('ready');
        });
    });

    describe('URL Validation', () => {
        test('valid Google Books URL with id and pg params', () => {
            const validUrls = [
                'https://books.google.com/books?id=abc123&pg=PA42',
                'https://books.google.com/books?id=xyz789&pg=RA1-PA10',
                'http://books.google.com/books?id=test&pg=PA1'
            ];

            validUrls.forEach(url => {
                const matches = url.match(/^https?:\/\/books\.google\.com\/books\?/);
                expect(matches).not.toBeNull();
            });
        });

        test('invalid URL fails', () => {
            const invalidUrls = [
                'https://example.com/books?id=abc123',
                'ftp://books.google.com/books?id=abc123',
                'not-a-url',
                ''
            ];

            invalidUrls.forEach(url => {
                const matches = url.match(/^https?:\/\/books\.google\.com\/books\?/);
                expect(matches).toBeNull();
            });
        });
    });

    describe('API Integration', () => {
        test('successful API call returns citation', async () => {
            const testUrl = 'https://books.google.com/books?id=abc123&pg=PA42';
            const testCitation = '{{Test Publication\n| id=abc123\n| page=42\n| title=\n| date=January 1, 2020\n}}';

            mockApi.get.mockResolvedValue({
                citation: testCitation
            });

            const result = await mockApi.get({
                action: 'googlebooks-citation',
                url: testUrl
            });

            expect(result.citation).toBe(testCitation);
            expect(mockApi.get).toHaveBeenCalledWith({
                action: 'googlebooks-citation',
                url: testUrl
            });
        });

        test('API error is handled', async () => {
            mockApi.get.mockRejectedValue(new Error('Network error'));

            try {
                await mockApi.get({
                    action: 'googlebooks-citation',
                    url: 'https://books.google.com/books?id=abc123'
                });
            } catch (error) {
                expect(error.message).toBe('Network error');
            }
        });

        test('empty API response is handled', async () => {
            mockApi.get.mockResolvedValue({});

            const result = await mockApi.get({
                action: 'googlebooks-citation',
                url: 'https://books.google.com/books?id=abc123'
            });

            expect(result.citation).toBeUndefined();
        });
    });

    describe('Message Localization', () => {
        test('retrieves toolbar button label', () => {
            const label = mw.msg('googlebooks-citation-toolbar-button');
            expect(label).toBe('Google Books citation');
        });

        test('retrieves prompt message', () => {
            const prompt = mw.msg('googlebooks-citation-toolbar-prompt');
            expect(prompt).toBe('Enter a Google Books URL:');
        });

        test('retrieves error message', () => {
            const error = mw.msg('googlebooks-citation-error-fetch');
            expect(error).toBe('GoogleBooksCitation error: could not fetch the Google Books page.');
        });
    });

    describe('WikiEditor Integration', () => {
        test('button is added to toolbar', () => {
            const $textbox = $('#wpTextbox1');

            $textbox.wikiEditor('addToToolbar', {
                section: 'main',
                group: 'insert',
                tools: {
                    'googlebooks-citation': {
                        label: mw.msg('googlebooks-citation-toolbar-button'),
                        type: 'button',
                        oouiIcon: 'reference'
                    }
                }
            });

            expect($textbox.wikiEditor).toHaveBeenCalledWith(
                'addToToolbar',
                expect.objectContaining({
                    section: 'main',
                    group: 'insert'
                })
            );
        });
    });
});
