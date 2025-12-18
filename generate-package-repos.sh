#!/bin/bash
# Generate composer package repos with require sections from feature branches
# Updates the repositories section in composer.itcr-1084.json

BRANCH="feature/ITCR-1084-deprecate-entity-browser"
INPUT_FILE="composer.itcr-1084.json"
OUTPUT_FILE="composer.itcr-1084.json"

if [ ! -f "$INPUT_FILE" ]; then
  echo "Error: $INPUT_FILE not found"
  exit 1
fi

# Define repos: name|version|type|url|raw_url_base
REPOS=(
  "open-y-subprojects/openy_features|5.0.0|drupal-module|https://github.com/open-y-subprojects/openy_features.git|https://raw.githubusercontent.com/open-y-subprojects/openy_features"
  "open-y-subprojects/openy_focal_point|2.0.0|drupal-module|https://github.com/open-y-subprojects/openy_focal_point.git|https://raw.githubusercontent.com/open-y-subprojects/openy_focal_point"
  "ycloudyusa/y_lb|5.0.0|drupal-module|https://github.com/YCloudYUSA/y_lb.git|https://raw.githubusercontent.com/YCloudYUSA/y_lb"
  "ycloudyusa/lb_claro|3.0.0|drupal-theme|https://github.com/YCloudYUSA/lb_claro.git|https://raw.githubusercontent.com/YCloudYUSA/lb_claro"
  "drupal/lb_accordion|3.0.0|drupal-module|https://git.drupalcode.org/project/lb_accordion.git|https://git.drupalcode.org/project/lb_accordion/-/raw"
  "drupal/lb_branch_amenities_blocks|3.0.0|drupal-module|https://git.drupalcode.org/project/lb_branch_amenities_blocks.git|https://git.drupalcode.org/project/lb_branch_amenities_blocks/-/raw"
  "drupal/lb_cards|3.0.0|drupal-module|https://git.drupalcode.org/project/lb_cards.git|https://git.drupalcode.org/project/lb_cards/-/raw"
  "drupal/lb_carousel|3.0.0|drupal-module|https://git.drupalcode.org/project/lb_carousel.git|https://git.drupalcode.org/project/lb_carousel/-/raw"
  "drupal/lb_grid_cta|4.0.0|drupal-module|https://git.drupalcode.org/project/lb_grid_cta.git|https://git.drupalcode.org/project/lb_grid_cta/-/raw"
  "drupal/lb_hero|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_hero.git|https://git.drupalcode.org/project/lb_hero/-/raw"
  "drupal/lb_modal|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_modal.git|https://git.drupalcode.org/project/lb_modal/-/raw"
  "drupal/lb_partners_blocks|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_partners_blocks.git|https://git.drupalcode.org/project/lb_partners_blocks/-/raw"
  "drupal/lb_ping_pong|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_ping_pong.git|https://git.drupalcode.org/project/lb_ping_pong/-/raw"
  "drupal/lb_promo|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_promo.git|https://git.drupalcode.org/project/lb_promo/-/raw"
  "drupal/lb_related_articles_blocks|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_related_articles_blocks.git|https://git.drupalcode.org/project/lb_related_articles_blocks/-/raw"
  "drupal/lb_related_events_blocks|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_related_events_blocks.git|https://git.drupalcode.org/project/lb_related_events_blocks/-/raw"
  "drupal/lb_simple_menu|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_simple_menu.git|https://git.drupalcode.org/project/lb_simple_menu/-/raw"
  "drupal/lb_staff_members_blocks|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_staff_members_blocks.git|https://git.drupalcode.org/project/lb_staff_members_blocks/-/raw"
  "drupal/lb_statistics|3.0.0|drupal-module|https://git.drupalcode.org/project/lb_statistics.git|https://git.drupalcode.org/project/lb_statistics/-/raw"
  "drupal/lb_testimonial_blocks|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_testimonial_blocks.git|https://git.drupalcode.org/project/lb_testimonial_blocks/-/raw"
  "drupal/lb_webform|2.0.0|drupal-module|https://git.drupalcode.org/project/lb_webform.git|https://git.drupalcode.org/project/lb_webform/-/raw"
  "drupal/ws_colorway_canada|2.0.0|drupal-theme|https://git.drupalcode.org/project/ws_colorway_canada.git|https://git.drupalcode.org/project/ws_colorway_canada/-/raw"
  "drupal/ws_event|2.0.0|drupal-module|https://git.drupalcode.org/project/ws_event.git|https://git.drupalcode.org/project/ws_event/-/raw"
  "drupal/ws_lb_tabs|3.0.0|drupal-module|https://git.drupalcode.org/project/ws_lb_tabs.git|https://git.drupalcode.org/project/ws_lb_tabs/-/raw"
  "drupal/ws_promotion|2.0.0|drupal-module|https://git.drupalcode.org/project/ws_promotion.git|https://git.drupalcode.org/project/ws_promotion/-/raw"
  "drupal/ws_small_y|2.0.0|drupal-module|https://git.drupalcode.org/project/ws_small_y.git|https://git.drupalcode.org/project/ws_small_y/-/raw"
  "drupal/y_camp|3.0.0|drupal-module|https://git.drupalcode.org/project/y_camp.git|https://git.drupalcode.org/project/y_camp/-/raw"
  "drupal/y_donate|3.0.0|drupal-module|https://git.drupalcode.org/project/y_donate.git|https://git.drupalcode.org/project/y_donate/-/raw"
  "drupal/y_facility|3.0.0|drupal-module|https://git.drupalcode.org/project/y_facility.git|https://git.drupalcode.org/project/y_facility/-/raw"
  "drupal/y_lb_article|2.0.0|drupal-module|https://git.drupalcode.org/project/y_lb_article.git|https://git.drupalcode.org/project/y_lb_article/-/raw"
  "drupal/y_program|2.0.0|drupal-module|https://git.drupalcode.org/project/y_program.git|https://git.drupalcode.org/project/y_program/-/raw"
  "drupal/y_program_subcategory|2.0.0|drupal-module|https://git.drupalcode.org/project/y_program_subcategory.git|https://git.drupalcode.org/project/y_program_subcategory/-/raw"
)

echo "Generating package repos with require sections..."
echo "Fetching composer.json from $BRANCH branch for each module..."

# Build new repositories array
REPOS_JSON="["

# Add VCS for yusaopeny first
REPOS_JSON+="{\"type\": \"vcs\", \"url\": \"https://github.com/YCloudYUSA/yusaopeny.git\"},"

# Add yusaopeny library packages
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"grt107/grt-youtube-popup\", \"type\": \"drupal-library\", \"version\": \"1.0.0\", \"source\": {\"type\": \"git\", \"url\": \"https://github.com/grt107/grt-youtube-popup\", \"reference\": \"d5cb51ae5dbe526dba7d82c646ec0f46791fa7a0\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-jaypan/jquery_colorpicker\", \"type\": \"drupal-library\", \"version\": \"1.0.1\", \"source\": {\"type\": \"git\", \"url\": \"https://github.com/jaypan/jquery_colorpicker\", \"reference\": \"da978ae124c57817021b3166a31881876882f5f9\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-ckeditor/panelbutton\", \"type\": \"drupal-library\", \"version\": \"4.10.1\", \"dist\": {\"type\": \"zip\", \"url\": \"https://download.ckeditor.com/panelbutton/releases/panelbutton_4.10.1.zip\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-ckeditor/colorbutton\", \"type\": \"drupal-library\", \"version\": \"4.10.1\", \"dist\": {\"type\": \"zip\", \"url\": \"https://download.ckeditor.com/colorbutton/releases/colorbutton_4.10.1.zip\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-ckeditor/font\", \"type\": \"drupal-library\", \"version\": \"4.12.1\", \"dist\": {\"type\": \"zip\", \"url\": \"https://download.ckeditor.com/font/releases/font_4.12.1.zip\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-smonetti/btbutton\", \"type\": \"drupal-library\", \"version\": \"1.0.2\", \"source\": {\"type\": \"git\", \"url\": \"https://github.com/smonetti/btbutton\", \"reference\": \"ee1fc396231a63201373cb580684e699a696ed62\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-davekoelle/alphanum\", \"type\": \"drupal-library\", \"version\": \"1.0.0\", \"dist\": {\"type\": \"zip\", \"url\": \"https://github.com/AndreyMaximov/alphanum/archive/1.0.0.zip\"}}},"
REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"library-vakata/jstree\", \"type\": \"drupal-library\", \"version\": \"3.3.8\", \"dist\": {\"type\": \"zip\", \"url\": \"https://github.com/vakata/jstree/archive/3.3.8.zip\"}}},"

for REPO in "${REPOS[@]}"; do
  IFS='|' read -r NAME VERSION TYPE URL RAW_BASE <<< "$REPO"

  # Build raw URL for composer.json
  RAW_URL="${RAW_BASE}/${BRANCH}/composer.json"

  echo "  Fetching: $NAME"

  # Fetch composer.json and extract require
  REQUIRE=$(curl -s "$RAW_URL" 2>/dev/null | jq -c '.require // {}')

  if [ -z "$REQUIRE" ] || [ "$REQUIRE" == "null" ]; then
    REQUIRE="{}"
  fi

  # Add package repo JSON
  REPOS_JSON+="{\"type\": \"package\", \"package\": {\"name\": \"${NAME}\", \"version\": \"${VERSION}\", \"type\": \"${TYPE}\", \"require\": ${REQUIRE}, \"source\": {\"url\": \"${URL}\", \"type\": \"git\", \"reference\": \"${BRANCH}\"}}},"
done

# Add composer repos
REPOS_JSON+="{\"type\": \"composer\", \"url\": \"https://packages.drupal.org/8\", \"canonical\": false},"
REPOS_JSON+="{\"type\": \"composer\", \"url\": \"https://asset-packagist.org\"}"

REPOS_JSON+="]"

# Update composer.itcr-1084.json with new repositories
echo "Updating $OUTPUT_FILE..."
jq --argjson repos "$REPOS_JSON" '.repositories = $repos' "$INPUT_FILE" > "${OUTPUT_FILE}.tmp" && mv "${OUTPUT_FILE}.tmp" "$OUTPUT_FILE"

echo "Done! Updated $OUTPUT_FILE with package repos including require sections."
echo "Run 'COMPOSER=composer.itcr-1084.json composer install' to test the configuration."
