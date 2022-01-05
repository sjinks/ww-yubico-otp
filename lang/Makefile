DOMAIN_FE    := ww-yubiotp-front
DOMAIN_BE    := ww-yubiotp-admin
PO_FILES_FE  := $(wildcard $(DOMAIN_FE)-*.po)
PO_FILES_BE  := $(wildcard $(DOMAIN_BE)-*.po)
MO_FILES_FE  := $(patsubst %.po,%.mo,$(PO_FILES_FE))
MO_FILES_BE  := $(patsubst %.po,%.mo,$(PO_FILES_BE))

POT_FE       := $(DOMAIN_FE).pot
POT_BE       := $(DOMAIN_BE).pot

MSGFMT       := msgfmt
MSGMERGE     := msgmerge

.PHONY: all clean

all: $(POT_FE) $(POT_BE) $(MO_FILES_FE) $(MO_FILES_BE)

$(POT_FE):
	"$(CURDIR)/../vendor/bin/wp" i18n make-pot "$(CURDIR)/../" "$(CURDIR)/../lang/$(POT_FE)" --domain="$(DOMAIN_FE)" --skip-js

$(POT_BE):
	"$(CURDIR)/../vendor/bin/wp" i18n make-pot "$(CURDIR)/../" "$(CURDIR)/../lang/$(POT_BE)" --domain="$(DOMAIN_BE)" --skip-js

$(MO_FILES_FE): $(PO_FILES_FE)
$(MO_FILES_BE): $(PO_FILES_BE)

%.mo: %.po
	$(MSGFMT) -o$@ $<

$(PO_FILES_FE): %: $(POT_FE)
	$(MSGMERGE) -U "$@" $(POT_FE)
	touch "$@"

$(PO_FILES_BE): %: $(POT_BE)
	$(MSGMERGE) -U "$@" $(POT_BE)
	touch "$@"

clean:
	-rm -f $(POT_FE) $(POT_BE) $(MO_FILES_FE) $(MO_FILES_BE)
