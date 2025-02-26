import { onDomReadyRetrieveUserInfo } from "./events/domReadyRetrieveUserInfo.js";
import { onDomReadyActiveItem } from "./events/domReadyActiveItem.js";
import { onDomReadyDailyReward } from "./events/domReadyDailyReward.js"; 
import { onDomReadyCountdown } from "./events/domReadyCountdown.js"; 

// Inizializza l'app con i moduli
onDomReadyRetrieveUserInfo();
onDomReadyActiveItem();
onDomReadyCountdown();
onDomReadyDailyReward();

