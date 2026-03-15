// SPDX-License-Identifier: GPL-2.0-only

module.exports = {
    testEnvironment: 'jsdom',
    testMatch: ['**/tests/jest/**/*.test.js'],
    collectCoverageFrom: [
        'resources/**/*.js'
    ],
    coverageDirectory: 'coverage',
    setupFilesAfterEnv: ['<rootDir>/tests/jest/setup.js']
};
