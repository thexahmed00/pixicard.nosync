import ar from "@src/locales/ar.json";
import bn from "@src/locales/bn.json";
import de from "@src/locales/de.json";
import en from "@src/locales/en.json";
import es from "@src/locales/es.json";
import fa from "@src/locales/fa.json";
import fr from "@src/locales/fr.json";
import he from "@src/locales/he.json";
import hi_IN from "@src/locales/hi_IN.json";
import it from "@src/locales/it.json";
import ja from "@src/locales/ja.json";
import nl from "@src/locales/nl.json";
import pl from "@src/locales/pl.json";
import pt_BR from "@src/locales/pt_BR.json";
import ru from "@src/locales/ru.json";
import sin from "@src/locales/sin.json";
import tr from "@src/locales/tr.json";
import uk from "@src/locales/uk.json";
import zh_CN from "@src/locales/zh_CN.json";
import { createI18n } from 'vue-i18n';
import { useCookies } from '@src/composable/cookies';

/**
 * Import the locales
 */
const locales = {
    ar, bn, de, en, es, fa, fr, he, hi_IN, it, ja, nl, pl, pt_BR, ru, sin, tr, uk, zh_CN,
};

/**
 * Get the cookies
 */
const cookies = useCookies();

/**
 * Get the locale
 */
let currentLocale = JSON.parse(cookies.get('locale'));

let locale = currentLocale?.code || 'en';
let direction = currentLocale?.direction || 'ltr';

/**
 * set the document html lang and dir attributes
 */
document.documentElement.lang = locale;
document.documentElement.dir = direction;

/**
 * Create the i18n instance
 */
export default createI18n({
    locale: locale,
    fallbackLocale: 'en',
    messages: locales,
    legacy: false,
});
