<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>

  <ContentBlock :content-title="title">

    <div class="flagcounter adminTable">
      <p>{{ translate('FlagCounter_PluginDescription') }}</p>

      <p>{{ translate('FlagCounter_GeneratorDescription') }}</p>

      <div class="form-group row">
        <label class="website-label"><strong>{{ translate('General_Website') }}</strong></label>
        <SiteSelector
            id="flagcounter"
            class="sites_autocomplete"
            v-model="site"
            :show-all-sites-item="false"
            :switch-site-on-select="false"
            :show-selected-site="true"
        />
      </div>

      <div class="form-group row">
        <div class="col s6">
          <Field
              uicontrol="number"
              name="rows"
              :model-value="rows"
              @update:model-value="rows = $event;"
              :title="translate('FlagCounter_NumberOfRows')"
              :min="1"
              :max="20"
              :full-width="true"
          />
        </div>
        <div class="col s6">
          <Field
              uicontrol="number"
              name="cols"
              :model-value="cols"
              @update:model-value="cols = $event;"
              :title="translate('FlagCounter_NumberOfColumns')"
              :min="1"
              :max="10"
              :full-width="true"
          />
        </div>
      </div>
      <div>
        <Field
            uicontrol="select"
            name="font"
            :model-value="font"
            :options="fonts"
            @update:model-value="font = $event;"
            :title="translate('FlagCounter_Font')"
            :full-width="true"
        />
      </div>

      <div class="form-group row">
        <div class="col s6">
          <Field
              uicontrol="number"
              name="fontsize"
              :model-value="fontsize"
              @update:model-value="fontsize = $event;"
              :title="translate('FlagCounter_FontSize')"
              :min="2"
              :max="30"
              :full-width="true"
          />
        </div>
        <div class="col s6">
          <div class="col s6">
            <Field
                uicontrol="text"
                name="fontcolor"
                :model-value="fontcolor"
                @update:model-value="fontcolor = $event;"
                :title="translate('FlagCounter_FontColor')"
                :full-width="true"
            />
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col s6">
          <Field
              uicontrol="text"
              name="date"
              :model-value="date"
              @update:model-value="date = $event;"
              :title="translate('General_Date')"
              :full-width="true"
          />
        </div>
        <div class="col s6">
          <Field
              uicontrol="select"
              name="period"
              :model-value="period"
              @update:model-value="period = $event;"
              :title="translate('General_Period')"
              :options="periodsNames"
              :full-width="true"
          />
        </div>
      </div>
      <div>
        <Field
            uicontrol="checkbox"
            name="showcode"
            :model-value="showcode"
            @update:model-value="showcode = $event;"
            :title="translate('FlagCounter_ShowCountryCode')"
            :full-width="true"
        />
      </div>
      <div>
        <Field
            uicontrol="checkbox"
            name="showcode"
            :model-value="hideflag"
            @update:model-value="hideflag = $event;"
            :title="translate('FlagCounter_HideFlag')"
            :full-width="true"
        />
      </div>

    </div>

    <strong>{{ translate('FlagCounter_UrlToImage') }}:</strong><br />
    <div><pre v-copy-to-clipboard="{}" v-text="flagCounterUrl"></pre></div>

    <p><img id="flagcounterimage" :src="flagCounterUrl" /></p>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  CopyToClipboard,
  SiteSelector,
  SiteRef,
  Matomo,
  MatomoUrl,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface UrlGeneratorState {
  site: SiteRef,
  rows: number,
  cols: number,
  font: string,
  fontsize: number,
  fontcolor: string,
  date: string,
  period: string,
  showcode: boolean,
  hideflag: boolean,
}

export default defineComponent({
  props: {
    title: String,
    fonts: Object,
    periodsNames: Object,
  },
  data(): UrlGeneratorState {
    return {
      site: {
        id: MatomoUrl.parsed.value.idSite as string,
        name: Matomo.helper.htmlDecode(Matomo.siteName),
      },
      rows: 5,
      cols: 2,
      font: '',
      fontsize: 12,
      fontcolor: '0,0,0',
      date: MatomoUrl.parsed.value.date as string,
      period: MatomoUrl.parsed.value.period as string,
      showcode: false,
      hideflag: false,
    };
  },
  components: {
    Field,
    ContentBlock,
    SiteSelector,
  },
  directives: {
    CopyToClipboard,
  },
  computed: {
    flagCounterUrl() {
      const prefix = window.location.href.split('?')[0];
      return `${prefix}?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        action: 'image',
        idSite: this.site.id,
        rows: this.rows,
        cols: this.cols,
        font: this.font,
        fontsize: this.fontsize,
        fontcolor: this.fontcolor,
        date: this.date,
        period: this.period,
        showcode: this.showcode ? 1 : 0,
        showflag: this.hideflag ? 0 : 1,
      })}`;
    },
  },
});
</script>
