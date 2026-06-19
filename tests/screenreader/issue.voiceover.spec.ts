import { voiceOverTest as test } from "@guidepup/playwright";
import {
    BASE_URL,
    ISSUE_PATH,
    assertIssueStructure,
    collectVoiceOverSpeech,
    expectSpoken,
} from "./lib/checks";

test.describe("Braillewright issue page — VoiceOver", () => {
    test("post landmark, headings and Back-to-top labels are exposed", async ({ page, voiceOver }) => {
        await page.goto(`${BASE_URL}${ISSUE_PATH}`, { waitUntil: "load" });

        // 1) Deterministic structure (DOM / accessibility tree).
        await assertIssueStructure(page);

        // 2) What VoiceOver announces walking into the article content.
        const speech = await collectVoiceOverSpeech(voiceOver);
        console.log(`[VoiceOver issue spoken log]\n${speech}`);
        expectSpoken(speech, ["skip"]);
    });
});
