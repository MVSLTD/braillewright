import { nvdaTest as test } from "@guidepup/playwright";
import {
    BASE_URL,
    ISSUE_PATH,
    assertIssueStructure,
    collectSpeechWalk,
    expectSpoken,
} from "./lib/checks";

test.describe("Braillewright issue page — NVDA", () => {
    test("post landmark, headings and Back-to-top labels are exposed", async ({ page, nvda }) => {
        await page.goto(`${BASE_URL}${ISSUE_PATH}`, { waitUntil: "load" });

        // 1) Deterministic structure (DOM / accessibility tree).
        await assertIssueStructure(page);

        // 2) What NVDA announces walking into the article content.
        const speech = await collectSpeechWalk(nvda, {
            maxSteps: 60,
            until: ["back to top", "post"],
        });
        console.log(`[NVDA issue spoken log]\n${speech}`);
        expectSpoken(speech, ["back to top"]);
    });
});
