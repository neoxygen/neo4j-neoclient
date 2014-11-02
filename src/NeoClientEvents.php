<?php

namespace Neoxygen\NeoClient;

final class NeoClientEvents
{
    const NEO_PRE_REQUEST_SEND = 'neoclient.pre_request_send';

    const NEO_POST_REQUEST_SEND = 'neoclient.post_request_send';

    const NEO_HTTP_EXCEPTION = 'neoclient.http_exception';

    const NEO_LOG_MESSAGE = 'neoclient.log_message';
}
