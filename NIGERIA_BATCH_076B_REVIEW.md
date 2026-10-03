# Research batch 076b — Bauchi: both names for the Zaar

Researched 2026-10-01, on the owner's instruction that the system keeps both variations.

- **Zaar people record:** 'Sayawa' is added as a recorded **exonym**. Its usage note says the federal profile and Wikipedia use it, and the Atlas says the Saya terms are now considered derogatory.
- **fix_076b, Bauchi State page:** the quoted state-government list changes from 'Sayawa' to **'Sayawa (Zaar)'**.
## Test (1 Oct 2026, on a fresh copy of live, with 076 in it)

- **Import:** 1 name. **fix_076b:** a dry run then apply changed admin_units #66, the Bauchi description. A second run changed nothing.
- **Pages:**
  - The Zaar page shows "Other names: Sayawa · exonym" with its usage note.
  - The Bauchi State page reads "Gerawa, Sayawa (Zaar), Jarawa".
- **Undo:** revert fix_076b, then roll back. Every table count is identical to the pre-import snapshot, and the description is restored.
