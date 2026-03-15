// SPDX-License-Identifier: GPL-2.0-only

/**
 * Test API response handling
 */

describe('API Response Handling', () => {
    let mockApi;

    beforeEach(() => {
        mockApi = {
            get: jest.fn()
        };
        mw.Api.mockReturnValue(mockApi);
    });

    test('handles citation with id and PA page', async () => {
        const citation = '{{Test Publication\n| id=abc123\n| page=42\n| title=\n| date=January 1, 2020\n}}';

        mockApi.get.mockResolvedValue({
            citation: citation
        });

        const result = await mockApi.get({
            action: 'googlebooks-citation',
            url: 'https://books.google.com/books?id=abc123&pg=PA42'
        });

        expect(result.citation).toContain('id=abc123');
        expect(result.citation).toContain('page=42');
        expect(result.citation).toContain('January 1, 2020');
    });

    test('handles citation with non-PA page prefix', async () => {
        const citation = '{{Test Publication\n| id=abc123\n| page_prefix=RA1-\n| page=10\n| title=\n| date=March 15, 2019\n}}';

        mockApi.get.mockResolvedValue({
            citation: citation
        });

        const result = await mockApi.get({
            action: 'googlebooks-citation',
            url: 'https://books.google.com/books?id=abc123&pg=RA1-10'
        });

        expect(result.citation).toContain('page_prefix=RA1-');
        expect(result.citation).toContain('page=10');
    });

    test('handles citation with no page parameter', async () => {
        const citation = '{{Test Publication\n| id=abc123\n| title=\n| date=June 5, 2015\n}}';

        mockApi.get.mockResolvedValue({
            citation: citation
        });

        const result = await mockApi.get({
            action: 'googlebooks-citation',
            url: 'https://books.google.com/books?id=abc123'
        });

        expect(result.citation).toContain('id=abc123');
        expect(result.citation).not.toContain('page=');
        expect(result.citation).toMatch(/\}\}$/);
        expect(result.citation).toMatch(/^\{\{/);
    });

    test('handles API failure gracefully', async () => {
        mockApi.get.mockRejectedValue({ error: { code: 'googlebooks-citation-error-fetch' } });

        try {
            await mockApi.get({
                action: 'googlebooks-citation',
                url: 'https://books.google.com/books?id=invalid'
            });
            fail('Expected promise to reject');
        } catch (error) {
            expect(error.error.code).toBe('googlebooks-citation-error-fetch');
        }
    });
});
