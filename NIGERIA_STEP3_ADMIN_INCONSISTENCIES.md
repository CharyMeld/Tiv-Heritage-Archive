# Master prompt — Step 3: administrative inconsistencies (flagged, NOT changed)

Checked on 25 September 2026, read-only, against the production database. Nothing was modified. Each item needs an owner decision before any change.

Sources used for the checks:
- The 1999 Constitution, s.3 and the First Schedule, from the nigeriarights.gov.ng PDF (text extracted with pdftotext).
- Statoids' LGA table (statoids.com/yng.html).
- Wikipedia's "Geopolitical zones of Nigeria".
- Earlier batch sources.

## A. Errors in our data (recommend correcting, on approval)

| # | Record | Problem | Evidence | Suggested fix |
|---|---|---|---|---|
| A1 | LGA "Ikot Abasi (Village)" (Akwa Ibom, id 918, slug ikot-abasi-village) | A qualifier is stored as part of the LGA name and appears in its summary. | Constitution First Schedule: "Ikot Abasi"; Statoids: "Ikot Abasi" | Rename to "Ikot Abasi". Keep the old address as a 301 redirect, and keep "Ikot Abasi (Village)" as a noted variant only if a source uses it. |
| A2 | FCT (id 169) | No capital set (capital_place_id is empty). All 36 states have one. | Constitution s.3(4) and Part II: Federal Capital Territory, **Abuja** | Add Abuja as the FCT's capital place, sourced to the Constitution. |
| A3 | Anambra State | created_on is 1976-02-03, the date of the *old* Anambra State. | The Anambra State Government and NIPC date the present state to 27 Aug 1991 (Enugu separated) | Owner decision, already pending since batch 013. |

## B. Spelling decisions for the owner (like the earlier Olamaboro and Ogbomosho decisions)

| # | LGA | Our name (= Constitution) | Other forms | Note |
|---|---|---|---|---|
| B1 | Ekiti | Idosi-Osi | Ido-Osi (NIPC), Ido Osi (Wikipedia; already stored as a variant) | Keep the Constitution's form, or switch to the common "Ido-Osi". |

## C. Spelling variants not yet recorded (add as searchable variants; no change to names)

Our names follow the Constitution and/or Wikipedia. Statoids uses these other forms, which are not yet stored as variants:
- Girie (Girei, Adamawa)
- Nassarawa (Nasarawa LGA, Kano; Nasarawa LGA, Nasarawa)
- Nassarawa Egon (Nasarawa Eggon)
- Emuoha (Emohua, Rivers)
- Barde (Bade, Yobe)
- Kauran Namoda (Kaura-Namoda, Zamfara)
- Abuja Municipal Area Council (AMAC) (FCT)

Not to be added:
- Statoids' "Zarki" for Kaduna's **Zaria**. It is Statoids' own error; the Constitution says Zaria.

## D. Questions about the administrative framework (research needed, then owner decision)

| # | Item | Issue |
|---|---|---|
| D1 | Bakassi LGA (Cross River) | It is still an LGA in the Constitution's schedule, but the peninsula was handed to Cameroon under the 2002 ICJ judgment and the 2006 Greentree Agreement (Statoids: "transferred ... 2006-06-12"). Our record shows it as current with no note. Suggestion: keep the record, which follows the Constitution, and add a sourced note or change record. This needs careful sourcing (it is already a gap from batch 008). |
| D2 | Taraba "Disputed Areas" | Statoids lists a 17th Taraba row, "Disputed Areas", which is not an LGA. Nothing to change; record it as a research gap (what the disputed areas are). |
| D3 | Five LGAs not found in the Constitution transcription | Gamawa (Bauchi), Kaura (Kaduna), Ilorin South (Kwara), Ibadan North-East (Oyo), Ilejemeje (Ekiti). They are recorded as "multiple sources" (Statoids + Wikipedia) because this PDF transcription omits or garbles them (for example, "Kaura, Namoda" in Zamfara). Nothing to change; an authoritative print of the First Schedule would let them be marked verified. |
| D4 | Our copy of the Constitution | The nigeriarights.gov.ng transcription has errors. Its list of the 36 states omits Delta and Oyo, and several LGA names are garbled ("Obia/Akpor", "Dange-shnsi", "Takali", "Ibadan Central", "Atigbo"). A better official copy is recommended as the primary source. |
| D5 | Kaduna rename date | The change "North-Central State → Kaduna State" has only the text "1976". The Kaduna government says "by 1976" and Wikipedia says 1975. Owner/research decision. |
| D6 | Historical regions | The Northern, Eastern and Western regions and the Mid-Western Region have no creation dates. The Mid-West was created by plebiscite in 1963 (Delta and Edo sources). The Western and Mid-Western regions rest on a single source. |
| D7 | LGA headquarters | No LGA has a headquarters recorded. Statoids gives headquarters for all 774. Recommend adding them as places in a later step, since the schema allows it. |
| D8 | Capital place type | The 36 capitals are typed "settlement" rather than "city". This is minor, for Step 4/5 (vocabulary). |
| D9 | Geopolitical zones | They are stored as a field on each state (all 37 agree with Wikipedia), not as records. The Constitution does not define them. For Step 4: decide whether zones become records (so they can hold sources and descriptions). |
| D10 | Coordinates | No place or unit has coordinates. For GIS enrichment later (brief §31). |

## E. Checked and consistent

- 36 states, the FCT, 768 LGAs and 6 area councils = 774, matching the Constitution and Statoids state by state.
- Every LGA sits under a current state. None is attached to an abolished state. Abolished states and regions have end dates.
- No duplicate LGA names within any state. Six names recur across states (Bassa, Ifelodun, Irepodun, Nasarawa, Obi, Surulere); these are different LGAs, correctly kept apart.
- All 37 zone tags match Wikipedia.
- Every published unit cites at least one source.
