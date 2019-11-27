<?php

namespace Vidal\MainBundle\Command\Seo;

class SslReplace
{
    const TO_SSL = array(
        "http://www.vidal.ru",
        "http://www.youtube.com",
        "http://www.vedomosti.ru",
        "http://www.urmc.rochester.ed",
        "http://www.theguardian.com",
        "http://www.sanofipasteur.com",
        "http://www.roche.ru",
        "http://www.plastsur.ru",
        "http://www.pediatr-mos.ru",
        "http://www.ncbi.nlm.nih.gov",
        "http://www.mosderma.ru",
        "http://www.moscowhealth.ru",
        "http://www.microsoft.com",
        "http://www.mamoclam.ru",
        "http://www.lvrach.ru",
        "http://www.ifdc.pro",
        "http://www.heel.de",
        "http://www.ft.com",
        "http://www.fda.gov",
        "http://www.facebook.com",
        "http://www.drugs.com",
        "http://www.clinicaltrials.gov",
        "http://www.cdc.gov",
        "http://www.boehringer-ingelheim.com",
        "http://www.ar-mos.com",
        "http://www.actelion.com",
        "http://www.accu-chek.ru",
        "http://www.accessdata.fda.gov",
        "http://vk.com",
        "http://session.webinar.ru",
        "http://seer.cancer.gov",
        "http://ru.wikipedia.org",
        "http://clinicaltrials.gov",
        "http://clinicalstudydatarequest.com",
        "http://www.mental-health-congress.ru",
        "http://www.businesswire.com",
        "http://roche.cvent.com",
        "http://onlinemd.ru",
        "http://news.bms.com",
        "http://www.zentiva.ru",
        "http://www.webmd.com",
        "http://www.univadis.ru",
        "http://www.uicc.org",
        "http://www.teva.ru",
        "http://www.southampton.ac.uk",
        "http://www.sheba-hospital.org.il",
        "http://www.sciencemag.org",
        "http://www.sacbee.com",
        "http://www.roche.com",
        "http://www.olympic.org",
        "http://www.novartis.ru",
        "http://www.novartis.com",
        "http://www.msd.ru",
        "http://www.medtronic.ru",
        "http://www.medscape.com",
        "http://www.medpagetoday.com",
        "http://www.mayoclinic.org",
        "http://www.kamelia.ru",
        "http://www.israeloncology.ru",
        "http://www.indiegogo.com",
        "http://www.hsph.harvard.edu",
        "http://www.hortus.ru",
        "http://www.hemofarm.com",
        "http://www.healthcare.philips.com",
        "http://www.galderma.com",
        "http://www.europeanpharmaceuticalreview.com",
        "http://www.covidien.com",
        "http://www.boehringer-ingelheim.ru",
        "http://www.bmj.com",
        "http://www.bayerhealthcare.ru",
        "http://www.aria-ayurveda.ru",
        "http://www.alz.co.uk",
        "http://www.alcon.com",
        "http://www.abbottnutrition.com",
        "http://world-sepsis-day.org",
        "http://ria.ru",
        "http://pss.sagepub.com",
        "http://pgu.mos.ru",
        "http://onlinelibrary.wiley.com",
        "http://mri.medagencies.org",
        "http://medicalxpress.com",
        "http://journal.publications.chestnet.or",
        "http://jama.jamanetwork.com",
        "http://hsci.ru",
        "http://gkb40.com",
        "http://diabetes.bayer.ru",
        "http://data.worldbank.org",
        "http://cts.businesswire.com",
        "http://bbc.com",
        "http://assuta-hospital.com",
        "http://assuta-hospital.com",
        "http://archpedi.jamanetwork.com",
        "http://1prime.ru",
        "http://www3.weforum.org",
        "http://www.who.int",
        "http://www.pfizer.com",
        "http://www.intercharm.ru",
        "http://www.gipertonik.ru",
        "http://www.ema.europa.eu",
        "http://webvidal.ru",
        "http://static.kremlin.ru",
        "http://products.sanofi.us",
        "http://mediexpo.ru",
        "http://gepatit-c.ru",
        "http://asozd2c.duma.gov.ru",
        "http://acto-russia.org",
        "http://xn--c1ajlbegbfjdu.xn--p1ai",
        "http://www.xobl.ru",
        "http://www.xn----gtbbbbyjumbnjby2e5c.xn--p1ai",
        "http://www.xenical.ru",
        "http://www.sunpharma.ru",
        "http://www.stopspid.ru",
        "http://www.ssmu.ru",
        "http://www.shreyalife.ru",
        "http://www.sciencedaily.com",
        "http://www.ru.lundbeck.com",
        "http://www.ru.all-biz.info",
        "http://www.remedim.ru",
        "http://www.rado-ee.org",
        "http://www.purolasa.cardio.ru",
        "http://www.polfa.pabianice.com.pl",
        "http://www.para-plus.ru",
        "http://www.medpharmconnect.com",
        "http://www.hc-sc.gc.ca",
        "http://www.lilly.ru",
        "http://www.lifefactor.ru",
        "http://www.jnjru.ru",
        "http://www.italfarmaco.com.ru",
        "http://www.hotlek.ru",
        "http://www.hivandhepatitis.com",
        "http://www.gripp.ru",
        "http://www.fz87.ru",
        "http://www.fstrf.ru",
        "http://www.firmbook.ru",
        "http://www.ferrosan.ru",
        "http://www.doctoronline.ru",
        "http://www.cscrussia.ru",
        "http://www.congress2016.rnmot.ru",
        "http://www.congress.rnmot.ru",
        "http://www.compactonair.ru",
        "http://www.ckb1.ru",
        "http://www.cardio-award.ru",
        "http://www.bloodstopper.ru",
        "http://www.bittner.ru",
        "http://www.biogenidec.com",
        "http://www.apteka.ua",
        "http://www.abstractsonline.com",
        "http://www.1med.tv",
        "http://web.archive.org",
        "http://view.yandex.net",
        "http://uzpharmsanoat.uz",
        "http://usndr.com",
        "http://pharmplaneta.ru",
        "http://onlinemd.ru",
        "http://old.calciumd3.ru",
        "http://medtusovka.ru",
        "http://medpharmconnect.com",
        "http://mail.google.com",
        "http://regulation.gov.ru",
        "http://euat.ru",
        "http://www.pharmvestnik.ru",
        "http://help-map.info",
        "http://grls.rosminzdrav.ru",
        "http://firmbook.ru",
        "http://emarketer.everzen.com",
        "http://bestinclass.ru"
    );

    public static function checkFile($url)
    {
        if($url && (strpos($url, '.png') === false) && (strpos($url, '.jpg') === false) &&
            (strpos($url, '.zip') === false) && (strpos($url, '.pdf') === false)
        ) {
            return false;
        }

        return true;
    }
}