import { voiceOverTest as test } from "@guidepup/playwright";
import {
    BASE_URL,
    ISSUE_BRAND,
    ISSUE_PATH,
    assertIssueStructure,
    collectHeadingWalk,
    expectSpoken,
} from "./lib/checks";

test.describe("Braillewright issue page — VoiceOver", () => {
    test("post landmark, headings and Back-to-top labels are exposed", async ({ page, voiceOver }) => {
        await page.goto(`${BASE_URL}${ISSUE_PATH}`, { waitUntil: "load" });

        // 1) Deterministic structure (DOM / accessibility tree).
        await assertIssueStructure(page);

        // 2) What VoiceOver announces walking into the article content.
        const speech = await collectHeadingWalk(voiceOver, voiceOver.keyboardCommands.findNextHeading);
        console.log(`[VoiceOver issue spoken log]\n${speech}`);
        expectSpoken(speech, ["heading", ISSUE_BRAND]);
    });
});
