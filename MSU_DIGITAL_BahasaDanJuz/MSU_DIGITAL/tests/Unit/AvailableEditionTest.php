const { assert } = require('chai');
const { AvailableEdition } = require('../../app/Models/AvailableEdition');

describe('AvailableEdition Model', () => {
    it('should create a new available edition', async () => {
        const edition = await AvailableEdition.create({ type: 'translation' });
        assert.equal(edition.type, 'translation');
    });
});