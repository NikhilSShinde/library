<?php

namespace App\PiplModules\newsletter;

use App\PiplModules\newsletter\Models\Subscriber;
use App\PiplModules\newsletter\Models\Newsletter;
use App\Jobs\Job;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use GlobalValues;


class SendNewsletterEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
	
	protected $subscribers,$newsletter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Newsletter $newsletter,$subscribers)
    {
        $this->subscribers = $subscribers;
		$this->newsletter = $newsletter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
            $site_email=GlobalValues::get('site-email');
            $site_title=GlobalValues::get('site-title');
            foreach( $this->subscribers as $subscriber)
            {
                   $the_news_letter = $this->newsletter;

                    $mailer->queue("newsletter::".$this->newsletter->id,array('email'=>$subscriber->email),function ($m) use ($subscriber,$the_news_letter,$site_email,$site_title ) {
                    $m->from($site_email, $site_title);
                    $m->to($subscriber->email)->subject($the_news_letter->subject);
                });

           }
    }
}
