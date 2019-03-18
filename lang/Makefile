DOMAIN_FRONT   := ww-yubiotp-front
DOMAIN_ADMIN   := ww-yubiotp-admin
POT_FRONT      := $(DOMAIN_FRONT).pot
POT_ADMIN      := $(DOMAIN_ADMIN).pot

PO_FILES_FRONT := $(wildcard $(DOMAIN_FRONT)-*.po)
PO_FILES_ADMIN := $(wildcard $(DOMAIN_ADMIN)-*.po)
MO_FILES       := $(patsubst %.po,%.mo,$(PO_FILES_FRONT) $(PO_FILES_ADMIN))

MSGFMT         := msgfmt
MSGMERGE       := msgmerge

.PHONY: all clean

all: $(MO_FILES)

$(MO_FILES): %.mo: %.po
	$(MSGFMT) -o$@ $<

$(PO_FILES_FRONT): %: $(POT_FRONT)
	$(MSGMERGE) -U "$@" $(POT_FRONT)
	touch "$@"

$(PO_FILES_ADMIN): %: $(POT_ADMIN)
	$(MSGMERGE) -U "$@" $(POT_ADMIN)
	touch "$@"

clean:
	-rm -f $(MO_FILES)
